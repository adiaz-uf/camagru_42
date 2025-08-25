FROM nginx:latest

COPY nginx/default.conf /etc/nginx/conf.d/default.conf

COPY frontend/src/ /usr/share/nginx/html/
COPY frontend/public/ /usr/share/nginx/html/images/
COPY backend/uploads/ /var/www/html/uploads/
COPY certs/ /etc/ssl/certs/