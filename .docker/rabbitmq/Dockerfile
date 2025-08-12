FROM rabbitmq:4-management-alpine
# https://registry.hub.docker.com/_/rabbitmq/

# Arguments
ARG UID=10001

# user
RUN <<EOF
    adduser -u ${UID} -G adm -s /bin/sh -D developer
EOF

USER $UID
