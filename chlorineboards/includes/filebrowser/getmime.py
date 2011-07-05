# a script for getting the mime type of a file
# copyright Swizec

import sys
import urllib, urllib2
import re

URL = sys.argv[ 1 ]
req = urllib2.Request ( URL )
url_handle = urllib2.urlopen ( req )
headers = url_handle.info ( )
p = re.compile ( 'Content-Type:\s+(.*)(;|$)', re.IGNORECASE|re.MULTILINE )
m = p.search ( str( headers ) )
	
print m.group ( 1 )
#print headers
