FROM mattrayner/lamp:latest-1804

#COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
#COPY ./000-default.conf /etc/apache2/sites-enabled/000-default.conf

RUN rm -rf build 

RUN apt-get update && apt-get install -y python3 python3-pip
#RUN pip3 install matplotlib
RUN pip3 install mammoth
#RUN pip3 install IPython
#installing gmt and dependecies 
RUN apt-get install -y gmt gmt-dcw gmt-gshhg
RUN apt-get install -y ghostscript
RUN apt-get install -y cmake
RUN apt-get install -y libnetcdf-dev libnetcdff-dev 
RUN apt-get install -y curl
RUN apt-get install -y gdal-bin
RUN apt-get install -y graphicsmagick
RUN apt-get install -y python3-sphinx
RUN apt-get install -y ffmpeg

# need to actually get gmt
RUN wget https://github.com/GenericMappingTools/gmt/releases/download/6.1.1/gmt-6.1.1-src.tar.gz 
RUN tar -xvzf gmt-6.1.1-src.tar.gz
#RUN cd gmt-6.1.1 

#using the source file to create something useful 
RUN mkdir gmt-6.1.1/build
RUN cd gmt-6.1.1/build && cmake .. && cmake --build . && cmake --build . --target install 


#actually installing pygmt 
RUN pip3 install pygmt
#downloading pygplates
RUN wget -O pygplates-src.deb https://sourceforge.net/projects/gplates/files/pygplates/beta-revision-28/ubuntu/pygplates-py3-ubuntu-bionic_2.2_1_amd64.deb/download
RUN apt-get install -y ./pygplates-src.deb

RUN mkdir /.gmt && chown -R www-data /.gmt

#RUN apt-get install -y download.deb
#RUN apt-get install -f
RUN pip3 install geopandas

CMD ["/run.sh"]
