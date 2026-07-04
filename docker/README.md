Docker development for AEnhance

Quick start (build & run):

```bash
# from repo root
docker compose up -d --build

# view logs
docker compose logs -f
```

Notes:
- App PHP-FPM listens on port 9000 inside the `app` container (nginx forwards to `app:9000`).
- Signaling exposed on host port 3001.
- Adjust `.env` to point to `DB_HOST=db` and `REDIS_HOST=redis`.
- For production, build images in CI and run behind a reverse proxy with TLS termination.
