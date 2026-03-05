#!F:/pdf2pdf_env/Scripts/python.exe
from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
sys.path.append("F:\\pdf2pdf_env\\Lib\\site-packages")
import os
import shutil
from shutil import make_archive
import simplejson as json
import fitz
import mysql.connector
import pprint
import time
from mysql.connector import Error
import textwrap
from PIL import Image, ImageOps 
import numpy as np
#import cv2.cv2 as cv2
import barcode
from barcode.writer import ImageWriter
import qrcode
import hashlib 
#from datetime import datetime
from xml.dom.minidom import parseString
import openpyxl
from openpyxl import load_workbook
from openpyxl.styles import Font 
from openpyxl.styles import Alignment
#from datetime import datetime
import uuid
import pytz
#from PyPDF2 import PdfFileWriter, PdfFileReader
import socket
import requests
import datetime
from datetime import date
from PIL import ImageDraw, ImageFont
import math
import subprocess
import pyAesCrypt


#from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
#from cryptography.hazmat.backends import default_backend


#from Crypto.Cipher import AES
#from base64 import b64encode, b64decode
#from re import sub

from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
from cryptography.hazmat.backends import default_backend
from cryptography.hazmat.primitives import padding
from base64 import b64encode, b64decode



from reportlab.lib.pagesizes import letter
from reportlab.pdfgen import canvas


import aspose.pdf as ap
from pypdf import PdfReader

import PyPDF2

from tika import parser

#sys.argv[1] = template id
#sys.argv[2] = data file
#sys.argv[3] = session user id
#sys.argv[4] = entry type (Fresh/Proceed)
#sys.argv[5] = progress file
#sys.argv[6] = dbName
#sys.argv[7] = subdomain
#sys.argv[8] = directoryUrlForward
#sys.argv[9] = directoryUrlBackward
#sys.argv[10] = servername
#sys.argv[11] = username
#sys.argv[12] = password
#sys.argv[13] = siteid
#sys.argv[14] = username
#sys.argv[15] = printer_name
# python extract_and_place.py 1 FYBAF_1619953609.pdf 1
#print(sys.argv[2])





def aes_encrypt(key, plaintext):
    backend = default_backend()

    # Generate a random IV (Initialization Vector)
    iv = b'\x00' * 16  # You should use a secure random IV in a real-world scenario

    cipher = Cipher(algorithms.AES(key), modes.CFB(iv), backend=backend)
    encryptor = cipher.encryptor()

    # Pad the plaintext to the block size of the cipher
    padder = padding.PKCS7(128).padder()
    padded_plaintext = padder.update(plaintext) + padder.finalize()

    # Encrypt the padded plaintext
    ciphertext = encryptor.update(padded_plaintext) + encryptor.finalize()

    # Combine IV and ciphertext and encode in base64 for storage or transmission
    encrypted_message = b64encode(iv + ciphertext).decode('utf-8')

    return encrypted_message

def aes_decrypt(key, encrypted_message):
    backend = default_backend()

    # Decode base64 and separate IV and ciphertext
    encrypted_data = b64decode(encrypted_message)
    iv = encrypted_data[:16]
    ciphertext = encrypted_data[16:]

    cipher = Cipher(algorithms.AES(key), modes.CFB(iv), backend=backend)
    decryptor = cipher.decryptor()

    # Decrypt the ciphertext
    decrypted_padded_data = decryptor.update(ciphertext) + decryptor.finalize()

    # Unpad the decrypted data
    unpadder = padding.PKCS7(128).unpadder()
    decrypted_plaintext = unpadder.update(decrypted_padded_data) + unpadder.finalize()

    return decrypted_plaintext


def text_to_pdf(input_text, output_pdf_path):
    # Create a PDF document
    pdf_canvas = canvas.Canvas(output_pdf_path, pagesize=letter)

    # Set font and size
    pdf_canvas.setFont("Helvetica", 12)

    # Split the input text into lines
    lines = input_text.split('\n')

    # Set initial y-coordinate for the text
    y_coordinate = 750  # Adjust as needed

    # Write each line to the PDF
    for line in lines:
        pdf_canvas.drawString(100, y_coordinate, line)
        y_coordinate -= 15  # Adjust as needed for line spacing

    # Save the PDF
    pdf_canvas.save()


def file_get_contents(filename):
    with open(filename) as f:
        return f.read()

# Example usage:
key = b'2x9WtczqYTg7vWdu2MKAQusUPENnmWXY'
plaintext = b'This is a secret message.'

# Encrypt
encrypted_message = aes_encrypt(key, plaintext)
print(f'Encrypted message: {encrypted_message}')

# Decrypt
decrypted_message = aes_decrypt(key, encrypted_message)
print(f'Decrypted message: {decrypted_message.decode("utf-8")}')




# Output PDF path

pdfFile = "E:\\wamp64\\www\\uneb\\public\\demo\\backend\\pdf_file\\GUID0001.pdf"





#textMsg = file_get_contents(pdfFile)
#print(textMsg)

#f = open(pdfFile, "r")
#print(f.read())
#file = open(pdfFile, "rb")

#pdf_File_Object = open(pdfFile, 'rb')  

#pdf_Reader = PDF.PdfFileReader(pdf_File_Object)  
#reader = PdfReader(pdfFile)
#text = ""
#for page in reader.pages:
#    text += page.extract_text() + "\n"
#data = subprocess.Popen([pdfFile],shell=True)
#print(pdf_Reader)

#print(file.read())

#input_text = text

#b64_mystring = b64encode(input_text)


#print(b64_mystring)
output_pdf_path = "E:\\wamp64\\www\\uneb\\public\\output.pdf"

# Initialize document object
#document = ap.Document()

# Add page
#page = document.pages.add()

# Initialize textfragment object
#text_fragment = ap.text.TextFragment(input_text)

# Add text fragment to new page
#page.paragraphs.add(text_fragment)

# Save updated PDF
#document.save(output_pdf_path)

# Convert text to PDF
#text_to_pdf(input_text, output_pdf_path)

#print(f"PDF created successfully at {output_pdf_path}")


##rootDir = "E:/wamp64/www/uneb/public/demo/documents/"
##pyAesCrypt.decryptFile(sys.argv[1], rootDir+"/"+"encypted_2.pdf", "AJITNATH")




