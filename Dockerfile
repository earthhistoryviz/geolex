FROM mattrayner/lamp:latest-1804

#COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
#COPY ./000-default.conf /etc/apache2/sites-enabled/000-default.conf

RUN apt-get update && apt-get install -y python3 python3-pip
RUN pip3 install mammoth

CMD ["/run.sh"]
