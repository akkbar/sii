# if [ -f "/etc/my.cnf" ]; then
    chmod 644 /etc/my.cnf
# elif [ -f "/etc/mysql/my.cnf" ]; then
#     chmod 644 /etc/mysql/my.cnf
# else
#     echo "my.cnf not found; skipping chmod."
# fi
exec docker-entrypoint.sh "$@"
