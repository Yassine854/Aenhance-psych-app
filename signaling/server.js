import { createServer } from "http";
import { WebSocketServer, WebSocket } from "ws";

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
});

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

function cleanup(ws) {
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
  } else {
    broadcast(roomId, ws, {
      type: "peer-left",
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

  console.log("New websocket connection");

  ws._roomId = undefined;
  ws._role = undefined;
  ws._name = undefined;

  ws._media = {
    audioEnabled: true,
    videoEnabled: true,
  };


  ws.on("message", (raw) => {

    let msg;

    try {
      msg = JSON.parse(String(raw));
    } catch {
      return safeSend(ws, {
        type: "error",
        message: "Invalid JSON",
      });
    }


    if (!msg || typeof msg.type !== "string") {
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

      const roomId = String(msg.roomId || "").trim();

      if (!roomId) {
        return safeSend(ws, {
          type: "error",
          message: "Missing roomId",
        });
      }


      const role =
        String(msg.role || "").trim() || undefined;

      const name =
        String(msg.name || "").trim() || undefined;


      cleanup(ws);


      ws._roomId = roomId;
      ws._role = role;
      ws._name = name;

      ws._media = {
        audioEnabled: true,
        videoEnabled: true,
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
            existing.close();
          } catch {}

          cleanup(existing);
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



  ws.on("close", () => cleanup(ws));

  ws.on("error", () => cleanup(ws));

});



server.listen(port, "0.0.0.0", () => {

  console.log(
    `AEnhance signaling running on port ${port}`
  );

});