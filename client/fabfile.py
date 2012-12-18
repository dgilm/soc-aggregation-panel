import database


class Fab():

    def __init__(self, config):
        self.__config = config

    def __get_count_db(self, db_name, table, where=""):

        q = "SELECT count(*) FROM %s" % (table)
        if where:
            q += " WHERE %s" % (where)
        result = database.query(self.__config['database'][db_name], q)
        if not result:
            return 0
        return result[0][0]

    def __sanitize_alarm_name(self, alarm):
        extra_text = (
            'directive_event: ',
            'AV Policy violation, ',
            'AV Policy Violation, ',
            'AV Bruteforce, ',
            'AV Malware, ',
            'AVT-FEED ',
            ' against DST_IP',
            ' on SRC_IP',
            ' on DST_IP',
        )
        for t in extra_text:
            alarm = alarm.replace(t, "")
        alarm[0].upper()
        return alarm

    def __sanitize_dns_name(self, dns):

        import re

        extra_text = (
            r'the following \d+ netbios names have been gathered : ',
            r'THE FOLLOWING \d+ NETBIOS NAMES HAVE BEEN GATHERED : ',
        )
        for t in extra_text:
            dns = re.sub(t, '', dns)
        return dns.upper()

    def __exec_command(self, cmd):
        from commands import getoutput
        return getoutput(cmd).lstrip().rstrip()

    def __iface_traffic(self, flow="recv"):

        import time

        traffic_map = {'recv': 2, 'sent': 10}

        cmd = "cat /proc/net/dev | grep : | "
        cmd += "awk '{ SUM += $%d} END { print SUM }'" % traffic_map[flow]
        a = self.__exec_command(cmd)
        time.sleep(1)
        b = self.__exec_command(cmd)
        traffic = "%.2f" % (float(int(b) - int(a)) / 1024)
        return traffic

    def hostname(self):
        import os
        return os.uname()[1]

    def distro(self):
        import commands
        return commands.getoutput('lsb_release -s -d')

    def loadaverage(self):
        import os
        (one, five, ten) = os.getloadavg()
        # return "%2f, %2f, %2f" % (one, five, fifteen)
        return five

    def cpupercent(self):
        import psutil
        cpu = int(psutil.cpu_percent())
        return str(cpu)

    def memorypercent(self):

        import psutil
        from distutils.version import StrictVersion

        if StrictVersion(psutil.__version__) < '0.4':  # deprecated
            total_mem = psutil.avail_phymem() + \
                psutil.cached_phymem() + psutil.phymem_buffers()
            return psutil.used_phymem() * 100 / total_mem
        else:
            return int(psutil.phymem_usage().percent)

    def usedmemory(self):

        import psutil
        from distutils.version import StrictVersion

        # print sizes in human readable (MB)
        MB = 1024 * 1024

        if StrictVersion(psutil.__version__) < '0.4':  # deprecated
            total_mem = psutil.avail_phymem() + \
                psutil.cached_phymem() + psutil.phymem_buffers()
            return "%d of %d" % (psutil.used_phymem() / MB, total_mem / MB)
        else:
            # I don't care about cached memory..
            return "%d of %d" % \
                ((psutil.phymem_usage().used -
                  psutil.cached_phymem() -
                  psutil.phymem_buffers()) / MB,
                 psutil.phymem_usage().total / MB)

    def processes(self):
        import psutil
        return len(psutil.get_process_list())

    def count_siem_events(self):
        return self.__get_count_db('snort', 'acid_event')

    def count_backlog_events(self):
        return self.__get_count_db('ossim', 'backlog_event')

    def count_alarms(self):
        return self.__get_count_db('ossim', 'alarm', 'status="open"')

    def count_incidents(self):
        return self.__get_count_db('ossim', 'incident', 'status="Open"')

# TODO:
# * queuedevents
# * eps
# * server-get-sensor-plugins
#
# * Hesaul quiere saber cuando un sensor deja de reportar:
#
# SELECT DISTINCT acid_event.sid, count(acid_event.cid) as event_cnt,
# count(distinct acid_event.plugin_id, acid_event.plugin_sid) as sig_cnt,
# count(distinct(acid_event.ip_src)) as saddr_cnt,
# count(distinct(acid_event.ip_dst)) as daddr_cnt, min(timestamp) as
# first_timestamp, max(timestamp) as last_timestamp FROM acid_event WHERE 1 AND
# ( timestamp >='2011-12-22 10:00:00' ) GROUP BY acid_event.sid ORDER BY
# event_cnt DESC
#
# * eventos del logger
# * /var/ossim/logger size
#

    def db_size(self, dbname):

        q = """
        SELECT table_schema "db_name",
            CAST((sum( data_length + index_length ) / 1024 / 1024) AS UNSIGNED) "db_size_MB"
        FROM information_schema.TABLES
            WHERE table_schema = "%s"
            GROUP BY table_schema
            ORDER BY db_size_MB DESC""" % (dbname)
        result = database.query(self.__config['database']['ossim'], q)
        return str(result[0][1])

    def num_vulnerabilities(self, host=None):

        # Returns latest vulnerability scanner result
        #
        # /* risk translation table */
        # $risks = array (
        #    "7" => _("Info"),
        #    "6" => _("Low"),
        #    "3" => _("Medium"),
        #    "2" => _("High"),
        #    "1" => _("Serious")
        # )
        #
        risk_table = {"info": 7, "low": 6, "medium": 3, "high": 2, "serious": 1}

        # filter by host
        where = "WHERE falsepositive='N'"
        if host is not None:
            where += " AND hostIP = '%s'" % (host)

        q = """
SELECT total, serious, high, medium, low FROM
    (SELECT count(*) AS total FROM
        (SELECT DISTINCT port, protocol, app, scriptid, msg, risk, hostIP
         FROM vuln_nessus_latest_results
         %s AND risk < 7) AS total) AS total,
    (SELECT count(*) AS serious FROM
        (SELECT DISTINCT port, protocol, app, scriptid, msg, risk, hostIP
         FROM vuln_nessus_latest_results
         %s AND risk=1) AS serious) AS serious,
    (SELECT count(*) AS high FROM
        (SELECT DISTINCT port, protocol, app, scriptid, msg, risk, hostIP
         FROM vuln_nessus_latest_results
         %s AND risk=2) AS high) AS high,
    (SELECT count(*) AS medium FROM
        (SELECT DISTINCT port, protocol, app, scriptid, msg, risk, hostIP
         FROM vuln_nessus_latest_results
         %s AND risk=3) AS medium) AS medium,
    (SELECT count(*) AS low FROM
        (SELECT DISTINCT port, protocol, app, scriptid, msg, risk, hostIP
         FROM vuln_nessus_latest_results
         %s AND risk=6) AS low) AS low
        """ % (where, where, where, where, where)

        result = database.query(self.__config['database']['ossim'], q)
        return {
            'total':    result[0][0],
            'serious':  result[0][1],
            'high':     result[0][2],
            'medium':   result[0][3],
            'low':      result[0][4],
        }

    def vulnerabilities(self):

        q = """SELECT hostname, hostIP, max(risk) AS risk
                   FROM vuln_nessus_latest_results GROUP BY (hostIP)""";

        result = database.query(
            config=self.__config['database']['ossim'],
            query=q
        )

        if not result:
            return []

        vulnerabilities = []

        for row in result:
            if row[0] is not None:
                num_vulns = self.num_vulnerabilities(host=row[1])
                vulnerabilities.append({
                    'hostname':         self.__sanitize_dns_name(row[0]),
                    'host_ip':          row[1],
                    'risk':             row[2],
                    'num_vuln_total':   num_vulns['total'],
                    'num_vuln_serious': num_vulns['serious'],
                    'num_vuln_high':    num_vulns['high'],
                    'num_vuln_medium':  num_vulns['medium'],
                    'num_vuln_low':     num_vulns['low'],
                })

        return vulnerabilities

    def sensors(self):
        q = "SELECT ip FROM sensor"
        result = database.query(self.__config['database']['ossim'], q)
        if not result:
            return []
        return result[0]

    def num_sensors(self):
        return len(self.sensors())

    def connected_sensors(self):
        import socket
        import re

        result = []

        endpoint = (self.__config['ossim-server']['host'],
                    self.__config['ossim-server']['port'])

        try:
            conn = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            conn.connect(endpoint)
        except socket.error:
            print " [!!] Error connecting to endpoint: %s" % repr(endpoint)
            return []

        conn.send('connect type="web"' + "\n")
        data = conn.recv(1024)
        conn.send('server-get-sensors' + "\n")
        data = conn.recv(1024)

        for line in data.split('\n'):
            r = re.findall('host="(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})"', line)
            if r:
                result.append(r[0])

        conn.close()
        return result

    def num_connected_sensors(self):
        return len(self.connected_sensors())

    def alarms(self, limit=None, filter="by_name"):

        group = "GROUP BY source, destination, alarm_name"
        if filter == "by_name":
            group = "GROUP BY alarm_name"
        elif filter == "by_source":
            group = "GROUP BY source"
        elif filter == "by destination":
            group = "GROUP BY destination"

        q = """
            SELECT count(*) AS count,
                   max(alarm.timestamp) AS date,
                   inet_ntoa(alarm.src_ip) AS source,
                   inet_ntoa(alarm.dst_ip) AS destination,
                   max(alarm.risk) AS risk,
                   sid.name AS alarm_name
            FROM plugin_sid AS sid, alarm AS alarm, event AS event
            WHERE event.id = alarm.event_id AND
                  event.plugin_id = sid.plugin_id AND
                  event.plugin_sid = sid.sid AND
                  alarm.status='open'
            %s
            ORDER BY count DESC
                  """ % group

        if limit is not None and limit.isdigit:
            q += " LIMIT %d" % int(limit)

        result = database.query(
            config=self.__config['database']['ossim'],
            query=q
        )

        if not result:
            return []

        alarms = []

        for row in result:
            if row[0] is not None:
                alarms.append({
                    'count': row[0],
                    'date': row[1].isoformat(sep=' '),
                    'source': row[2],
                    'destination': row[3],
                    'risk': row[4],
                    'alarm': self.__sanitize_alarm_name(row[5])
                })

        return alarms

    def riskiest_alarm(self):

        q = """
            SELECT sid.name AS alarm_name
            FROM plugin_sid AS sid, alarm AS alarm, event AS event
            WHERE event.id = alarm.event_id AND
                  event.plugin_id = sid.plugin_id AND
                  event.plugin_sid = sid.sid AND
                  alarm.status='open'
            ORDER BY alarm.risk DESC
            LIMIT 1
                  """
        result = database.query(self.__config['database']['ossim'], q)
        if not result:
            return []
        return self.__sanitize_alarm_name(result[0][0])

    def most_repeated_alarm(self):

        q = """
            SELECT count(sid.name) AS count, sid.name AS alarm_name
            FROM plugin_sid AS sid, alarm AS alarm, event AS event
            WHERE event.id = alarm.event_id AND
                  event.plugin_id = sid.plugin_id AND
                  event.plugin_sid = sid.sid AND
                  alarm.status='open'
            GROUP BY sid.name ORDER BY count DESC LIMIT 1
                  """
        result = database.query(self.__config['database']['ossim'], q)
        if not result:
            return []
        return self.__sanitize_alarm_name(result[0][1])

    def compromised_host(self):

        q = """
            SELECT count(*) AS count, inet_ntoa(alarm.src_ip) AS source
            FROM plugin_sid AS sid, alarm AS alarm, event AS event
            WHERE event.id = alarm.event_id AND
                  event.plugin_id = sid.plugin_id AND
                  event.plugin_sid = sid.sid AND
                  alarm.status='open'
            GROUP BY source ORDER BY count DESC LIMIT 1;
        """
        result = database.query(self.__config['database']['ossim'], q)
        if not result:
            return ""
        elif result[0][1] == "0.0.0.0":
            return ""
        return result[0][1]

    def attacked_host(self):

        q = """
            SELECT count(*) AS count, inet_ntoa(alarm.dst_ip) AS dest
            FROM plugin_sid AS sid, alarm AS alarm, event AS event
            WHERE event.id = alarm.event_id AND
                  event.plugin_id = sid.plugin_id AND
                  event.plugin_sid = sid.sid AND
                  alarm.status='open'
            GROUP BY dest ORDER BY count DESC LIMIT 1;
        """
        result = database.query(self.__config['database']['ossim'], q)
        if not result:
            return ""
        elif result[0][1] == "0.0.0.0":
            return ""
        return result[0][1]

    def kernel(self):
        return self.__exec_command('uname -ro | cut -d" " -f1')

    def ip(self):
        cmd = "LC_ALL=C /sbin/ifconfig | " +\
            "awk '/inet / && !/127.0.0.1/ {sub(/addr:/, \"\", $2); print $2}'"
        return self.__exec_command(cmd)

    def ossim_server_version(self):
        cmd = "dpkg -l | grep ossim-server | awk '{print $3}'"
        return self.__exec_command(cmd)

    def num_cores(self):
        cmd = 'cat /proc/cpuinfo | grep "^processor" | wc -l'
        return self.__exec_command(cmd)

    def model_name(self):
        import commands
        cmd = 'cat /proc/cpuinfo | grep "model name" | tail -n 1 | cut -d":" -f2'
        return commands.getoutput(cmd)

    def count_inst_pkg(self):

        distro = self.__exec_command("lsb_release -s -d").upper()

        if distro.startswith("Debian".upper()) or \
           distro.startswith("Ubuntu".upper()):
            return self.__exec_command("dpkg -l | wc -l")

        elif distro.startswith("Redhat".upper()) or \
           distro.startswith("Centos".upper()) or \
           distro.startswith("Fedora".upper()):
            return self.__exec_command("rpm -qa | wc -l")

        else:
            return 0

    def iface_recv(self):
        return self.__iface_traffic("recv")

    def iface_sent(self):
        return self.__iface_traffic("sent")

    def geolocation(self):
        import GeoIP
        gi = GeoIP.open("GeoLiteCity.dat", GeoIP.GEOIP_STANDARD)
        record = gi.record_by_addr(self.__config['client']['geoip'])
        # new record for google maps link
        record['geolocation'] = \
            str(record['latitude']) + ", " + str(record['longitude'])
        return record
