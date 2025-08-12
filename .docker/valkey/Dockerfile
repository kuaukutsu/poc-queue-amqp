FROM valkey/valkey:8.1-alpine3.21
# https://github.com/valkey-io/valkey/blob/unstable/valkey.conf

# Arguments
ARG UID=10001

# user
RUN <<EOF
    adduser -u ${UID} -G adm -s /bin/sh -D developer
EOF

USER $UID
