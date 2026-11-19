FROM nginx:1.27-alpine

# Le point d'entree officiel de l'image nginx execute envsubst sur
# /etc/nginx/templates/*.template -> /etc/nginx/conf.d/*.conf
COPY nginx.render.conf.template /etc/nginx/templates/default.conf.template

EXPOSE 80
