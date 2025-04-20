#!/bin/bash

# Check if required environment variables are set
check_required_vars() {
    local required_vars=("ADMIN_USERNAME" "ADMIN_PASSWORD" "DB_HOSTNAME")
    local missing_vars=()

    for var in "${required_vars[@]}"; do
        if [ -z "${!var}" ]; then
            missing_vars+=("$var")
        fi
    done

    if [ ${#missing_vars[@]} -ne 0 ]; then
        echo "Error: Required environment variables are not set:"
        printf ' - %s\n' "${missing_vars[@]}"
        exit 1
    fi
}

# Validate environment variables
check_required_vars

# Wait for database connection
if [ -n "$DB_HOSTNAME" ]; then
  until nc -z -v -w30 "$DB_HOSTNAME" 3306; do
    echo "Waiting for database connection..."
    sleep 5
  done
fi

# Run init script
echo "Running admin create script"
php /var/www/api/init.php

# Start main app
echo "Starting application"
exec "$@"