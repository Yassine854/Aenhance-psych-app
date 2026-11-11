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
- The frontend sends reactions and raised-hand updates as versioned `call-interaction` chat payloads. Existing signaling deployments relay these intact, so these controls do not require a signaling restart. Deploy the frontend and refresh both participants' call pages.
- Interaction payloads are consumed before chat rendering. Reactions are temporary; raised hands are resynchronized when signaling reconnects or another participant joins.
- Expose a secure `wss://` endpoint through a reverse proxy (nginx/traefik)
- Validate JWTs from Laravel when accepting WebSocket connections
