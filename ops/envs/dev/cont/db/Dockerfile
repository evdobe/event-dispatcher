FROM postgres:9.6.24
RUN localedef -i el_GR -c -f UTF-8 -A /usr/share/locale/locale.alias el_GR.UTF-8
ENV LANG el_GR.utf8
COPY target /