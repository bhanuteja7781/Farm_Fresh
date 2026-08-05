#!/bin/bash
echo "Deploying FarmFresh to AWS EC2..."
git pull origin main
docker-compose down
docker-compose up -d --build
echo "Deployment Complete!"
