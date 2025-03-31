#!/usr/bin/env python
#
#-------------------------------------------------------------------------------
#  Pi.Alert
#  Open Source Network Guard / WIFI & LAN intrusion detector and Web service monitor
#
#  pialert.py - Back module. Network scanner, Web service monitor
#-------------------------------------------------------------------------------
#  Puche 2021                                              GNU GPLv3
#  leiweibau 2023                                          GNU GPLv3
#-------------------------------------------------------------------------------

#===============================================================================
# IMPORTS
#===============================================================================
from __future__ import print_function
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from requests.packages.urllib3.exceptions import InsecureRequestWarning
from time import sleep, time, strftime
from base64 import b64encode
try:
  from urlparse import urlparse
except ImportError:
  from urllib.parse import urlparse
import sys, subprocess, os, re, datetime, socket, io, smtplib, requests, time, pwd, glob

#===============================================================================
# CONFIG CONSTANTS
#===============================================================================
PIALERT_BACK_PATH = os.path.dirname(os.path.abspath(__file__))
PIALERT_PATH = f"{PIALERT_BACK_PATH}/.."
PIALERT_WEBSERVICES_LOG = f"{PIALERT_PATH}/log/pialert.webservices.log"
STOPPIALERT = f"{PIALERT_PATH}/config/setting_stoppialert"
PIALERT_DB_FILE = f"{PIALERT_PATH}/db/pialert.db"
REPORTPATH_WEBGUI = f"{PIALERT_PATH}/front/reports/"

if (sys.version_info > (3,0)):
  exec(open(f"{PIALERT_PATH}/config/version.conf").read())
  exec(open(f"{PIALERT_PATH}/config/pialert.conf").read())
else:
  execfile(f"{PIALERT_PATH}/config/version.conf")
  execfile(f"{PIALERT_PATH}/config/pialert.conf")

#===============================================================================
# MAIN
#===============================================================================
def main():
    global startTime
    global cycle
    global log_timestamp

    # Header
    print('\nPi.Alert v'+ VERSION_DATE)
    print('---------------------------------------------------------')
    print(f"Executing user: {get_username()}\n")
    
    # Initialize global variables
    log_timestamp  = datetime.datetime.now()

    # Timestamp
    startTime = datetime.datetime.now()
    startTime = startTime.replace (second=0, microsecond=0)

    # Check parameters
    if len(sys.argv) != 2 :
        print ('usage pialert reporting_test' )
        return
    cycle = str(sys.argv[1])

    ## Main Commands
    if cycle == 'reporting_test':
        res = sending_notifications_test('Test')
    elif cycle == 'reporting_starttimer':
        res = sending_notifications_test('noti_Timerstart')
    elif cycle == 'reporting_stoptimer':
        res = sending_notifications_test('noti_Timerstop')
    else:
        res = 0

    # Final menssage
    print ('\nDONE!!!\n\n')
    return 0    

#===============================================================================
# Set Env (Userpermissions DB-file)
#===============================================================================
def get_username():
    return pwd.getpwuid(os.getuid())[0]

# ------------------------------------------------------------------------------
def set_reports_file_permissions():
  os.system(f"sudo chown -R {get_username()}:www-data {REPORTPATH_WEBGUI}")
  os.system(f"sudo chmod -R 775 {REPORTPATH_WEBGUI}")

#===============================================================================
# Sending Notifications
#===============================================================================
def sending_notifications_test(_Mode):
    if _Mode == 'Test' :
        notiMessage = "Test-Notification"
    elif _Mode == 'noti_Timerstart' :
        notiMessage = "Pi.Alert is paused"
    elif _Mode == 'noti_Timerstop' :
        notiMessage = "Pi.Alert reactivated"

    print ('\nTest Reporting...')
    if REPORT_MAIL or REPORT_MAIL_WEBMON:
        print ('    Sending report by email...')
        send_email (notiMessage, notiMessage)
    else :
        print ('    Skip mail...')
    if REPORT_PUSHSAFER or REPORT_PUSHSAFER_WEBMON:
        print ('    Sending report by PUSHSAFER...')
        send_pushsafer_test (notiMessage)
    else :
        print ('    Skip PUSHSAFER...')
    if REPORT_PUSHOVER or REPORT_PUSHOVER_WEBMON:
        print ('    Sending report by PUSHOVER...')
        send_pushover_test (notiMessage)
    else :
        print ('    Skip PUSHOVER...')
    if REPORT_TELEGRAM or REPORT_TELEGRAM_WEBMON:
        print ('    Sending report by Telegram...')
        send_telegram_test (notiMessage)
    else :
        print ('    Skip Telegram...')
    if REPORT_NTFY or REPORT_NTFY_WEBMON:
        print ('    Sending report by NTFY...')
        send_ntfy_test (notiMessage)
    else :
        print ('    Skip NTFY...')
    if REPORT_WEBGUI or REPORT_WEBGUI_WEBMON:
        print ('    Save report to file...')
        send_webgui_test (notiMessage)
    else :
        print ('    Skip WebGUI...')        
    return 0

#-------------------------------------------------------------------------------
def send_ntfy_test(_notiMessage):
    headers = {
        "Title": "Pi.Alert Notification",
        "Priority": NTFY_PRIORITY,
        "Tags": "warning"
    }

    if NTFY_CLICKABLE == True:
        headers["Click"] = REPORT_DASHBOARD_URL
    #if NTFY_USER != "" and NTFY_PASSWORD != "":
    if NTFY_PASSWORD != "":
    # Generate hash for basic auth
        usernamepassword = f"{NTFY_USER}:{NTFY_PASSWORD}"
        basichash = b64encode(bytes(f'{NTFY_USER}:{NTFY_PASSWORD}',
                                "utf-8")).decode("ascii")

    # add authorization header with hash
        headers["Authorization"] = f"Basic {basichash}"

    requests.post(f"{NTFY_HOST}/{NTFY_TOPIC}", data=_notiMessage, headers=headers)

#-------------------------------------------------------------------------------
def send_pushsafer_test(_notiMessage):
    try:
        notification_target = PUSHSAFER_DEVICE
    except NameError:
        notification_target = "a"

    try:
        result = PUSHSAFER_PRIO
    except NameError:
        PUSHSAFER_PRIO = 0

    try:
        notification_sound = PUSHSAFER_SOUND
    except NameError:
        notification_sound = 22

    url = 'https://www.pushsafer.com/api'
    post_fields = {
        "t" : 'Pi.Alert Message',
        "m" : _notiMessage,
        "s" : notification_sound,
        "v" : 3,
        "i" : 148,
        "c" : '#ef7f7f',
        "d" : notification_target,
        "u" : REPORT_DASHBOARD_URL,
        "ut" : 'Open Pi.Alert',
        "k" : PUSHSAFER_TOKEN,
        "pr" : PUSHSAFER_PRIO,
        }
    requests.post(url, data=post_fields)

#-------------------------------------------------------------------------------
def send_pushover_test(_notiMessage):
    try:
        result = PUSHOVER_PRIO
    except NameError:
        PUSHOVER_PRIO = 0

    try:
        notification_sound = PUSHOVER_SOUND
    except NameError:
        notification_sound = 'siren'

    url = 'https://api.pushover.net/1/messages.json'
    post_fields = {
        "token": PUSHOVER_TOKEN,
        "user": PUSHOVER_USER,
        "title" : 'Pi.Alert Message',
        "message" : _notiMessage,
        "priority" : PUSHOVER_PRIO,
        "sound" : notification_sound,
        }
    requests.post(url, data=post_fields)

#-------------------------------------------------------------------------------
def send_telegram_test(_notiMessage):
  runningpath = os.path.abspath(os.path.dirname(__file__))
  stream = os.popen(
      f'{runningpath}/shoutrrr/{SHOUTRRR_BINARY}/shoutrrr send --url "{TELEGRAM_BOT_TOKEN_URL}" --message "{_notiMessage}" --title "Pi.Alert"'
  )

#-------------------------------------------------------------------------------
def send_webgui_test(_notiMessage):
  _webgui_filename = time.strftime("%Y%m%d-%H%M%S") + "_Test.txt"
  if (os.path.exists(REPORTPATH_WEBGUI + _webgui_filename) == False):
    with open(REPORTPATH_WEBGUI + _webgui_filename, "w") as f:
      f.write(_notiMessage)
  set_reports_file_permissions()

#-------------------------------------------------------------------------------
def remove_tag(pText, pTag):
    # return text without the tag
  return pText.replace(f'<{pTag}>', '').replace(f'</{pTag}>', '')

#-------------------------------------------------------------------------------
def write_file(pPath, pText):
    # Write the text depending using the correct python version
  if sys.version_info < (3, 0):
    file = io.open (pPath , mode='w', encoding='utf-8')
    file.write ( pText.decode('unicode_escape') )
  else:
    file = open (pPath, 'w', encoding='utf-8')
    file.write (pText) 

  file.close() 

#-------------------------------------------------------------------------------
def append_line_to_file(pPath, pText):
    # append the line depending using the correct python version
  if sys.version_info < (3, 0):
    file = io.open (pPath , mode='a', encoding='utf-8')
    file.write ( pText.decode('unicode_escape') )
  else:
    file = open (pPath, 'a', encoding='utf-8')
    file.write (pText) 

  file.close() 

#-------------------------------------------------------------------------------
def send_email(pText, pHTML):
    # Compose email
    msg = MIMEMultipart('alternative')
    msg['Subject'] = 'Pi.Alert Report'
    msg['From'] = REPORT_FROM
    msg['To'] = REPORT_TO
    msg.attach (MIMEText (pText, 'plain'))
    msg.attach (MIMEText (pHTML, 'html'))

    # Send mail
    smtp_connection = smtplib.SMTP (SMTP_SERVER, SMTP_PORT)
    smtp_connection.ehlo()
    if not SafeParseGlobalBool("SMTP_SKIP_TLS"):
        smtp_connection.starttls()
        smtp_connection.ehlo()
    if not SafeParseGlobalBool("SMTP_SKIP_LOGIN"):
        escaped_password = repr(SMTP_PASS)[1:-1]
        smtp_connection.login (SMTP_USER, escaped_password)
    smtp_connection.sendmail (REPORT_FROM, REPORT_TO, msg.as_string())
    smtp_connection.quit()

#-------------------------------------------------------------------------------
def SafeParseGlobalBool(boolVariable):
  return eval(boolVariable) if boolVariable in globals() else False

#===============================================================================
# UTIL
#===============================================================================
def print_log(pText):
    global log_timestamp

    # Check LOG actived
    if not PRINT_LOG :
        return

    # Current Time    
    log_timestamp2 = datetime.datetime.now()

    # Print line + time + elapsed time + text
    print ('--------------------> ',
        log_timestamp2, ' ',
        log_timestamp2 - log_timestamp, ' ',
        pText)

    # Save current time to calculate elapsed time until next log
    log_timestamp = log_timestamp2

#===============================================================================
# BEGIN
#===============================================================================
if __name__ == '__main__':
    sys.exit(main())       
