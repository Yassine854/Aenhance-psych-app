Signaling service for AEnhance

Quick start (local):

- Install deps: `npm install`
- Start: `npm run start`

Docker:

- Build: `docker build -t aenhance-signaling .`
- Run: `docker run -p 3001:3001 --env SIGNALING_PORT=3001 aenhance-signaling`

Production with PM2:

- `npm ci --only=production`
- `pm2 start pm2.config.js`

Notes:
- Expose a secure `wss://` endpoint through a reverse proxy (nginx/traefik)
- Validate JWTs from Laravel when accepting WebSocket connections
