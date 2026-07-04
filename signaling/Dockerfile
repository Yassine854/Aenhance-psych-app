FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY . .

ENV SIGNALING_PORT=3001
ENV SIGNALING_HOST=0.0.0.0

EXPOSE 3001

CMD ["node", "server.js"]
