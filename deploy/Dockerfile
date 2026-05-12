FROM python:3-slim
RUN apt-get update && apt-get install -y --no-install-recommends git \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY webhook.py .
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENV REPO_PATH=/repo
EXPOSE 9000
ENV PYTHONUNBUFFERED=1
ENTRYPOINT ["/entrypoint.sh"]
