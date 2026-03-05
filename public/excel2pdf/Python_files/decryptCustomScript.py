#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe
from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
import os
import pytz
import pyAesCrypt

# Specify timezone
tz_IND = pytz.timezone('Asia/Calcutta') 

# File paths from arguments
input_file = sys.argv[1]
output_file = sys.argv[2]

# print(input_file)
# print(output_file)
# (Optional) Read file content just to print or verify

if not os.path.exists(input_file):
    print(f"Error: The input file does not exist: {input_file}")
    
try:
    with open(input_file, "rb") as f:
        content = f.read()
        print(f"File read successfully, size: {len(content)} bytes")
except UnicodeDecodeError as e:
    print(f"Error reading file:first")





output_dir = os.path.dirname(output_file)
if not os.path.exists(output_dir):
    print(f"Error: The output directory does not exist: {output_dir}")
# Encrypt the file
password = "AJITNATH"
try:
    pyAesCrypt.decryptFile(input_file, output_file, password)
    
    print(f"File encrypted successfully: {output_file}")
except Exception as e:
    print(f"Error during encryption: {e}")