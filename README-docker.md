# Docker deployment for FarmFresh

## Prerequisites
- Docker Engine
- Docker Compose
- An EC2 instance with ports 80 and 3306 open

## Local run
```bash
docker compose up --build -d
```

## Access
- Frontend: http://localhost
- Backend API: http://localhost/api/...
- MySQL: localhost:3306

## EC2 deployment steps
1. Install Docker and Docker Compose on Ubuntu EC2.
2. Clone this repository on the EC2 instance.
3. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
4. Start containers:
   ```bash
   docker compose up --build -d
   ```
5. Open HTTP port 80 in the EC2 security group.

## Useful commands
```bash
docker compose ps
docker compose logs -f
docker compose down
docker compose down -v
```
