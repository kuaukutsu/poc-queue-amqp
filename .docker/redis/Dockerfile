FROM redis:8-alpine3.21

# Arguments
ARG UID=10001

# user
RUN <<EOF
    adduser -u ${UID} -G adm -s /bin/sh -D developer
EOF

USER $UID
