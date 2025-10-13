#!/bin/sh

# Redirect all output to the container's log so we can see it with "docker logs"
exec >> /proc/1/fd/1 2>&1

echo "Cron script is starting..."

# Ensure the crontab file ends with a newline
echo "" >> /etc/cron.d/laravel-cron

# Start the cron daemon in the foreground
exec cron -f