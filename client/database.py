def query(config, query):

    import MySQLdb
    import sys

    conn = None
    result = None

    if not config.has_key('host') or not config.has_key('user') or \
       not config.has_key('pass') or not config.has_key('database'):
        print " [!!] Error reading database configuration"
        sys.exit(1)

    try:
        conn = MySQLdb.connect(config['host'],
                               config['user'],
                               config['pass'],
                               config['database'])
        cur = conn.cursor()
        cur.execute(query)
        result = cur.fetchall()

    except MySQLdb.Error, e:
        print " [!!] Error %d: %s" % (e.args[0], e.args[1])
        sys.exit(1)

    finally:
        if conn:
            conn.close()

    return result

