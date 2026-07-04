Hosting checklist for signaling service

1. Domain & TLS
- Create a subdomain like `signaling.example.com`.
- Terminate TLS at reverse proxy (nginx/traefik) and proxy `wss://` to service.

2. Reverse proxy
- Configure nginx or Traefik to proxy `/` or a specific path to `ws://localhost:3001` and upgrade WebSocket headers.

3. Scaling & processes
- For small scale, run single Node process with PM2 or Docker.
- For larger scale, use multiple replicas behind a sticky-session-aware load balancer or run with Redis-based pub/sub for cross-instance signaling.

4. Security
- Require JWT token on WebSocket connect (send in query param or Authorization header).
- Validate token against Laravel public key or secret.
- Rate-limit connections and validate room IDs.

5. Monitoring
- Use logs (stdout) and a process manager to restart on failure.
- Add health endpoint or use TCP health check on port.

6. Deployment
- Use Docker image and orchestrator (docker-compose, swarm, k8s).
- Use CI to build/publish image and ensure proper env injection.

7. Dev
- For local dev, run `docker-compose -f docker-compose.signaling.yml up --build`
