FROM nginx:alpine

# Create directory for Laravel logs if needed
RUN mkdir -p /var/log/nginx

# Copy default Nginx configuration
COPY default.conf /etc/nginx/conf.d/default.conf

# Expose HTTP port
EXPOSE 80

# Optional: add SSL later for HTTPS
# EXPOSE 443

# Start Nginx in foreground
CMD ["nginx", "-g", "daemon off;"]

