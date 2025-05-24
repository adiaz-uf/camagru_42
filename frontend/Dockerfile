# ./frontend/Dockerfile
FROM nginx:alpine

# Copiamos el contenido de src y public al directorio de Nginx
COPY src/ /usr/share/nginx/html/
#COPY frontend/public/ /usr/share/nginx/html/

# Exponer el puerto (opcional porque Nginx ya escucha en 80)
EXPOSE 80