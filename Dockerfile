FROM mattrayner/lamp:latest-1804

#COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
#COPY ./000-default.conf /etc/apache2/sites-enabled/000-default.conf

CMD ["/run.sh"]
