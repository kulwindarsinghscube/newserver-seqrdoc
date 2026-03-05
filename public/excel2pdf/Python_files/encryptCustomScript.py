#!D:/pdf2pdf_env/Scripts/python.exe
from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
sys.path.append("D:\\pdf2pdf_env\\Lib\\site-packages")
import os
import pytz
import pyAesCrypt

# Specify timezone
tz_IND = pytz.timezone('Asia/Calcutta') 

# File paths from arguments
input_file = sys.argv[1]
output_file = sys.argv[2]


print(input_file)
print(output_file)

# Define root directory
rootDir = "E:/wamp64/www/uneb/public/demo/documents/"

# (Optional) Read file content just to print or verify
try:
    with open(input_file, "r", encoding="utf-8") as f:
        print(f.read())  # Print content (optional)
except UnicodeDecodeError as e:
    print(f"Error reading file: {e}")

# Encrypt the file
password = "AJITNATH"
try:
    pyAesCrypt.encryptFile(input_file, output_file, password)
    print(f"File encrypted successfully: {output_file}")
except Exception as e:
    print(f"Error during encryption: {e}")