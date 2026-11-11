import { createServer } from "http";
import { WebSocketServer, WebSocket } from "ws";
import { inspectJoinToken } from "./auth.js";
import { startHeartbeat } from "./heartbeat.js";

const reactions = new Set(['thumbs-up', 'happy', 'sad', 'love', 'laugh', 'applause', 'celebrate', 'surprised']);

const authSecret = process.env.SIGNALING_AUTH_SECRET;
if (!authSecret || authSecret.length < 32) {
  throw new Error('SIGNALING_AUTH_SECRET must be configured with at least 32 characters.');
}

const port = Number(process.env.PORT || 3001);

// HTTP server for DirectAdmin/Passenger health checks
const server = createServer((req, res) => {
  res.writeHead(200, {
    "Content-Type": "text/plain",
  });
  res.end("AEnhance signaling server running");
});

// WebSocket server attached to HTTP server
const wss = new WebSocketServer({
  server,
  maxPayload: 64 * 1024,
  perMessageDeflate: false,
});
startHeartbeat(wss);

/**
 * @typedef {{ peers: Set<WebSocket>, roles: Map<string, WebSocket> }} Room
 */

/** @type {Map<string, Room>} */
const rooms = new Map();

console.log("AEnhance signaling starting...");

function safeSend(ws, obj) {
  try {
    if (ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify(obj));
    }
  } catch {
    // ignore
  }
}

function broadcast(roomId, sender, msg) {
  const room = rooms.get(roomId);
  if (!room) return;

  for (const ws of room.peers) {
    if (ws !== sender) {
      safeSend(ws, msg);
    }
  }
}

function cleanup(ws, notify = true) {
  clearTimeout(ws._disconnectTimer);
  const roomId = ws._roomId;

  if (!roomId) return;

  const room = rooms.get(roomId);

  if (!room) return;

  const role = ws._role;
  const name = ws._name;
  const media = ws._media;

  if (role && room.roles.get(role) === ws) {
    room.roles.delete(role);
  }

  room.peers.delete(ws);

  if (room.peers.size === 0) {
    rooms.delete(roomId);
  } else if (notify) {
    broadcast(roomId, ws, {
      type: "peer-left",
      signalingTimeout: ws._disconnectExpired === true,
      role,
      name,
      media,
    });
  }

  ws._roomId = undefined;
  ws._role = undefined;
  ws._name = undefined;
  ws._media = undefined;
}


wss.on("connection", (ws) => {
  let rateWindow = Date.now();
  let messageCount = 0;
  const joinTimeout = setTimeout(() => {
    if (!ws._roomId) ws.close(4008, 'Room join timed out');
  }, 15000);
  ws.on('close', () => clearTimeout(joinTimeout));

  console.log("New websocket connection");

  ws._roomId = undefined;
  ws._role = undefined;
  ws._name = undefined;

  ws._media = {
    audioEnabled: true,
    videoEnabled: true,
    handRaised: false,
  };


  ws.on("message", (raw) => {
    if (Date.now() - rateWindow >= 10000) {
      rateWindow = Date.now();
      messageCount = 0;
    }
    if (++messageCount > 300) return ws.close(4029, 'Too many messages');

    let msg;

    try {
      msg = JSON.parse(String(raw));
    } catch {
      return safeSend(ws, {
        type: "error",
        message: "Invalid JSON",
      });
    }


    if (!msg || typeof msg !== 'object' || Array.isArray(msg) || typeof msg.type !== "string") {
      return;
    }


    // Leave room
    if (msg.type === "leave") {

      cleanup(ws);

      return safeSend(ws, {
        type: "left",
      });
    }


    // Join room
    if (msg.type === "join") {
      const { identity, reason } = inspectJoinToken(msg.token, authSecret);
      if (!identity) {
        // Never log tokens, identity claims, credentials or secret hashes.
        console.warn('[signaling] Room authorization rejected', { reason });
        return ws.close(1008, 'Invalid or expired room authorization');
      }
      clearTimeout(joinTimeout);

      const roomId = identity.roomId;

      if (!roomId) {
        return safeSend(ws, {
          type: "error",
          message: "Missing roomId",
        });
      }



      const role = identity.role;

      const name = identity.name;


      cleanup(ws);


      ws._roomId = roomId;
      ws._role = role;
      ws._name = name;

      ws._media = {
        audioEnabled: true,
        videoEnabled: true,
        handRaised: false,
      };


      if (!rooms.has(roomId)) {
        rooms.set(roomId, {
          peers: new Set(),
          roles: new Map(),
        });
      }


      const room = rooms.get(roomId);


      // One user per role
      if (role) {

        const existing = room.roles.get(role);

        if (existing && existing !== ws) {

          try {
            existing.close(4001, 'Consultation opened elsewhere');
          } catch {}

          cleanup(existing, false);
        }


        room.roles.set(role, ws);
      }


      room.peers.add(ws);


      const peers = [];

      for (const peer of room.peers) {

        if (peer === ws) continue;

        peers.push({
          role: peer._role,
          name: peer._name,
          media: peer._media,
        });
      }


      safeSend(ws, {
        type: "joined",
        roomId,

        peerCount: room.peers.size,

        you: {
          role: ws._role,
          name: ws._name,
        },

        peers,
      });


      broadcast(roomId, ws, {
        type: "peer-joined",
        role,
        name,
        media: ws._media,
      });


      return;
    }



    const roomId = ws._roomId;


    if (!roomId) {

      return safeSend(ws, {
        type: "error",
        message: "Not joined",
      });

    }



    // WebRTC signaling
    if (
      msg.type === "restart-request" ||
      msg.type === "offer" ||
      msg.type === "answer" ||
      msg.type === "ice"
    ) {

      return broadcast(roomId, ws, {
        ...msg,

        from: {
          role: ws._role,
          name: ws._name,
          media: ws._media,
        },
      });

    }



    // Reactions are transient, room-scoped, and attributed to the signed identity.
    if (msg.type === 'reaction') {
      if (!reactions.has(msg.reaction) || Date.now() - (ws._lastReactionAt || 0) < 600) return;
      ws._lastReactionAt = Date.now();
      return broadcast(roomId, ws, {
        type: 'reaction', reaction: msg.reaction, role: ws._role, name: ws._name,
      });
    }

    // Chat
    if (msg.type === "chat") {

      return broadcast(roomId, ws, {
        type: "chat",
        payload: msg.payload,
      });

    }



    // Media state
    if (msg.type === "media") {

      const audioEnabled =
        msg.audioEnabled === false ? false : true;

      const videoEnabled =
        msg.videoEnabled === false ? false : true;


      ws._media = {
        audioEnabled,
        videoEnabled,
        handRaised: msg.handRaised === true,
      };


      return broadcast(roomId, ws, {
        type: "media",
        role: ws._role,
        name: ws._name,
        media: ws._media,
      });

    }



    // End session
    if (msg.type === "session-ended") {
      if (ws._role !== 'psychologist') return;

      return broadcast(roomId, ws, {
        type: "session-ended",
        endedAt: msg.endedAt || null,

        from: {
          role: ws._role,
          name: ws._name,
          media: ws._media,
        },
      });

    }

  });



  ws.on("close", () => {
    if (!ws._roomId) return;
    ws._disconnectTimer = setTimeout(() => {
      ws._disconnectExpired = true;
      cleanup(ws);
    }, 30000);
    ws._disconnectTimer.unref?.();
  });

  ws.on("error", () => { /* close handles cleanup after a reconnect grace period */ });

});



server.listen(port, "0.0.0.0", () => {

  console.log(
    `AEnhance signaling running on port ${server.address().port}`
  );

});
