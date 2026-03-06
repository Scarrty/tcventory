# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM nginx:1.27-alpine
WORKDIR /var/www/html
COPY public ./public
COPY --from=frontend /app/public/build ./public/build
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
