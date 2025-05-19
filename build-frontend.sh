#!/bin/bash
echo "Installing with dev depencendies to build with mix..."
NODE_OPTIONS=--max_old_space_size=2000 npm install

echo "Building frontend..."
NODE_OPTIONS=--max_old_space_size=2000 npm run prod

echo "Cleaning up non production dependencies..."
rm -rf node_modules
NODE_OPTIONS=--max_old_space_size=2000 npm install --production

echo "Deployment finished!"
