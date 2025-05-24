FROM php:8.3.9-apache
RUN apt update && apt upgrade -y
RUN apt install -y netcat-traditional
RUN docker-php-ext-install mysqli
#RUN a2enmod ssl

COPY ./public /var/www/html
COPY ./api /var/www/api
COPY ./entrypoint.sh /usr/local/bin

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]


EXPOSE 80
#EXPOSE 443