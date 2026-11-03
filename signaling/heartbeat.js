// Native WebSocket ping/pong keeps proxy connections active independently of chat.
export function startHeartbeat(wss, intervalMs = 25000) {
  wss.on('connection', (socket) => {
    socket.isAlive = true;
    socket.on('pong', () => { socket.isAlive = true; });
  });
  const timer = setInterval(() => {
    for (const socket of wss.clients) {
      if (socket.isAlive === false) {
        socket.terminate();
        continue;
      }
      socket.isAlive = false;
      socket.ping();
    }
  }, intervalMs);
  timer.unref?.();
  wss.on('close', () => clearInterval(timer));
}
