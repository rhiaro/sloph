FROM ubuntu:14.04

RUN apt-get update

# Install sshd
RUN apt-get install -y openssh-server vim
RUN mkdir /var/run/sshd

# Set password to 'admin'
RUN printf admin\\nadmin\\n | passwd

# Install MySQL
RUN apt-get install -y zlib1g-dev mysql-server mysql-client libmysqlclient-dev
# Install Apache
RUN apt-get install -y apache2
COPY apache-config.conf /etc/apache2/sites-enabled/000-default.conf
#ADD html /var/www/html
# Install php
RUN apt-get install -y php5 libapache2-mod-php5 php5-mcrypt php5-curl
COPY php.ini /etc/php5/apache2/php.ini

# Enable apache mods.
RUN a2enmod php5
RUN php5enmod mcrypt
RUN a2enmod rewrite

# Install phpMyAdmin
RUN mysqld & \
	service apache2 start; \
	sleep 5; \
	printf y\\n\\n\\n1\\n | apt-get install -y phpmyadmin; \
	sleep 15; \
	mysqladmin -u root shutdown

RUN sed -i "s#// \$cfg\['Servers'\]\[\$i\]\['AllowNoPassword'\] = TRUE;#\$cfg\['Servers'\]\[\$i\]\['AllowNoPassword'\] = TRUE;#g" /etc/phpmyadmin/config.inc.php 

RUN a2enmod headers

EXPOSE 22
EXPOSE 80
EXPOSE 3306

CMD mysqld_safe & \
	service apache2 start; \
	/usr/sbin/sshd -D
