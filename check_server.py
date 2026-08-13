import urllib.request
import json

# Let's inspect what the web server returns
req = urllib.request.urlopen("http://localhost:8000/users")
print("Response code:", req.getcode())
