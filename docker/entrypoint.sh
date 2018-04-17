#!/bin/bash
for file_name in /entrypoint.d/*sh
do
    if [ -e "${file_name}" ]; then
        echo " >> entrypoint.d - executing $file_name"
        . "${file_name}"
    fi
done

if [[ "${RUNTIME_EMULATED}" == "1" ]]; then
    echo " >> Running through qemu-arm-static..."
    exec /usr/bin/qemu-arm-static /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
fi

echo " >> Running supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
