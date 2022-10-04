FROM mattrayner/lamp:latest-1804

#COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
#COPY ./000-default.conf /etc/apache2/sites-enabled/000-default.conf

RUN rm -rf build 

RUN apt-get update && apt-get install -y python3 python3-pip
RUN apt-get install python3-setuptools
#RUN pip3 install matplotlib
RUN pip3 install mammoth
#RUN pip3 install IPython
#installing gmt and dependecies 
RUN apt-get install -y gmt gmt-dcw gmt-gshhg
RUN apt-get update && apt-get install -y ghostscript
RUN apt-get update && apt-get install -y cmake
RUN apt-get update && apt-get install -y libnetcdf-dev libnetcdff-dev 
RUN apt-get update && apt-get install -y curl

RUN apt-get install -y python3.8-dev
RUN add-apt-repository ppa:ubuntugis/ppa &&  apt-get update
#RUN apt-get install -y --allow-downgrades libssl1.1=1.1.1-1ubuntu2.1~18.04.9
RUN apt-get install -y --allow-downgrades libssl1.1=1.1.1-1ubuntu2.1~18.04.20
RUN apt-get install -y --allow-downgrades libssl-dev
RUN apt-get install -y libmysqlclient-dev
RUN apt-get install -y  gdal-bin=2.2.3+dfsg-2 libgdal20=2.2.3+dfsg-2 libgdal-dev=2.2.3+dfsg-2 libgdal-java=2.2.3+dfsg-2 gdal-data=2.2.3+dfsg-2 python-gdal=2.2.3+dfsg-2 python3-gdal=2.2.3+dfsg-2

RUN apt-get install -y graphicsmagick
RUN apt-get install -y python3-sphinx

RUN apt-get install -y ffmpeg

# need to actually get gmt
RUN wget https://github.com/GenericMappingTools/gmt/releases/download/6.4.0/gmt-6.4.0-src.tar.gz
RUN tar -xvzf gmt-6.4.0-src.tar.gz


#using the source file to create something useful 
RUN mkdir gmt-6.4.0/build
RUN cd gmt-6.4.0/build && cmake .. && cmake --build . && cmake --build . --target install 


#actually installing pygmt 
RUN pip3 install pygmt
#downloading pygplates
RUN wget -O pygplates-src.deb https://sourceforge.net/projects/gplates/files/pygplates/beta-revision-28/ubuntu/pygplates-py3-ubuntu-bionic_2.2_1_amd64.deb/download
RUN apt-get install -y ./pygplates-src.deb

RUN mkdir /.gmt && chown -R www-data /.gmt
RUN pip3 install numpy --upgrade

CMD ["/run.sh"]
