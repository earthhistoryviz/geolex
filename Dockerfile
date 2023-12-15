FROM mattrayner/lamp:latest-1804

# Cleanup
RUN rm -rf build 

# Install necessary system tools and libraries
RUN apt-get update && \
    apt-get install -y --no-install-recommends --allow-downgrades \
    python3 python3-pip gmt gmt-dcw gmt-gshhg ghostscript cmake \
    libnetcdf-dev libnetcdff-dev curl \
    libssl1.1 libssl-dev libmysqlclient-dev libpcre3=2:8.39-9ubuntu0.1 libxml2=2.9.4+dfsg1-6.1ubuntu1.9 \
    libdap-dev libpcre3-dev libxerces-c-dev libxml2-dev gdal-bin libgdal20 libgdal-dev libgdal-java gdal-data python3-gdal \
    graphicsmagick python3-sphinx ffmpeg software-properties-common && \
    pip3 install mammoth && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Add repository for GDAL and install
RUN add-apt-repository ppa:ubuntugis/ppa && apt-get update && \
    apt-get install -y --no-install-recommends gdal-bin=2.2.3+dfsg-2 libgdal20=2.2.3+dfsg-2 libgdal-dev=2.2.3+dfsg-2 \
    libgdal-java=2.2.3+dfsg-2 gdal-data=2.2.3+dfsg-2 python3-gdal=2.2.3+dfsg-2 && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install GMT
RUN wget https://github.com/GenericMappingTools/gmt/releases/download/6.1.1/gmt-6.1.1-src.tar.gz && \
	tar -xvzf gmt-6.1.1-src.tar.gz && \
	mkdir gmt-6.1.1/build && \
	apt-get update && apt-get install -y build-essential && \
	cd gmt-6.1.1/build && cmake .. && cmake --build . && cmake --build . --target install 

# Set path for PyGmt
ENV GMT_LIBRARY_PATH /usr/local/lib

# Install PyGMT and dependencies
RUN apt-get install -y python3-dev && \
	pip3 install --upgrade setuptools && \
    pip3 install numpy==1.15.4 Cython && \
    pip3 install netCDF4 cftime && \
    pip3 install pygmt==0.3.0

# Install PyGplates
RUN wget -O pygplates-src.deb https://sourceforge.net/projects/gplates/files/pygplates/beta-revision-28/ubuntu/pygplates-py3-ubuntu-bionic_2.2_1_amd64.deb/download && \
	apt-get install -y ./pygplates-src.deb

# Configure GMT permissions
RUN mkdir /.gmt && chown -R www-data /.gmt

CMD ["/run.sh"]