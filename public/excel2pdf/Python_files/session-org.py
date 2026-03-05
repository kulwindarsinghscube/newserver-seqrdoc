#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe

# importing the requests library
#import requests
#from requests import Session


# session = Session()

# # HEAD requests ask for *just* the headers, which is all you need to grab the
# # session cookie
# session.head('https://demo.seqrdoc.com/admin/store-file')

# response = session.get(
#     url='https://demo.seqrdoc.com/admin/store-file',
#     data={
#         'N': '4294966750',
#         'form-trigger': 'moreId',
#         'moreId': '156#327',
#         'pageType': 'EventClass'
#     },
#     headers={
#         'Referer': 'https://demo.seqrdoc.com/admin/store-file'
#     }
# )


#print(response.status_code, response.content)
#print(response.text)

# try:
#     #response = requests.get("https://demo.seqrdoc.com/admin/store-file")

#     # api-endpoint
#     URL = "https://demo.seqrdoc.com/admin/store-file"

#     # location given here
#     location = "delhi technological university"

#     pdf_path="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/demo/backend/pdf_file/10001.pdf";
#     # defining a params dict for the parameters to be sent to the API
#     PARAMS = {'pdf_path':pdf_path,'site_id':5}

#     # sending get request and saving the response as response object
#     response =requests.get(url = URL, params = PARAMS)

# except requests.exceptions.ConnectionError as e:
#     logger.exception()
#     print(-1, 'Connection Error')
# else:
#     print(response.status_code, response.content)

# # api-endpoint
# URL = "https://demo.seqrdoc.com/admin/store-file"

# # location given here
# location = "delhi technological university"

# # defining a params dict for the parameters to be sent to the API
# PARAMS = {'address':location}

# # sending get request and saving the response as response object
# r = requests.get(url = URL, params = PARAMS)

# # extracting data in json format
# data = r.json()


# extracting latitude, longitude and formatted address
# of the first matching location
# latitude = data['results'][0]['geometry']['location']['lat']
# longitude = data['results'][0]['geometry']['location']['lng']
# formatted_address = data['results'][0]['formatted_address']

# # printing the output
# print("Latitude:%s\nLongitude:%s\nFormatted Address:%s"
#     %(latitude, longitude,formatted_address))

# response = requests.get("https://demo.seqrdoc.com/admin/store-file")
# print(response)
#import sys

# import socket
# hostname = socket.gethostname()
# dns_resolved_addr = socket.gethostbyname(hostname)
# serversocket.bind((dns_resolved_addr, 8089))
# print(hostname)
#print(sys.argv[1])

#print('ABC')

from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
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
from datetime import datetime
from xml.dom.minidom import parseString
import openpyxl
from openpyxl import load_workbook
from openpyxl.styles import Font 
from openpyxl.styles import Alignment
from datetime import datetime
import uuid
import pytz
#from PyPDF2 import PdfFileWriter, PdfFileReader
import socket
import requests

#import datetime
#from datetime import date


tz_IND = pytz.timezone('Asia/Calcutta') 

try:

	connection = mysql.connector.connect(host="seqrdoc.com",
                                          database="seqr_demo",
                                          user="developer",
                                          password="developer")
	if connection.is_connected():
		db_Info = connection.get_server_info()
        cursor = connection.cursor()
        cursor.execute("select ep_details, id, extractor_details, template_name, pdf_page, print_bg_file, print_bg_status, verification_bg_file, verification_bg_status from uploaded_pdfs where id = '%s'" % (1))
        record = cursor.fetchone() 
        #print(1)
		# cursor = connection.cursor()
  #     	cursor.execute("select * from student_table where publish=1 order by id desc")
  #  	  	records = cursor.fetchall()  
  #  	  	row_count = cursor.rowcount
  #  	  	print(records)
 	 	connection.commit()

except Error as e:
    print("Error while connecting to MySQL", e)
	#
			
	        #db_Info = connection.get_server_info()
	        # cursor = connection.cursor()
	        # cursor.execute("select * from student_table where publish=1 order by id desc")
	       	#  records = cursor.fetchall()  
	       	#  row_count = cursor.rowcount
	       	#  print(records)
#function take input of the datestring like 2017-05-01
# def get_financial_year(datestring):
#             date = datetime.datetime.strptime(datestring, "%Y-%m-%d").date()
#             #initialize the current year
#             year_of_date=date.year
#             #initialize the current financial year start date
#             financial_year_start_date = datetime.datetime.strptime(str(year_of_date)+"-04-01","%Y-%m-%d").date()
#             if date<financial_year_start_date:
#                     return str(financial_year_start_date.year-1)[2:]+'-'+ str(financial_year_start_date.year)[2:]
#             else:
#                     return str(financial_year_start_date.year)[2:]+'-'+ str(financial_year_start_date.year+1)[2:]

# today = date.today()
# print(today)
# current_year=get_financial_year(str(today))


#current_date_time=datetime.now().strftime('%Y-%m-%d %H:%M:%S');
    #if connection.is_connected():
       
# if connection.is_connected():
# 	print('test')
# 	 cursor.execute("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '%s' " % (current_year))
 #     	records = cursor.fetchall()  
 #      row_count = cursor.rowcount
        # print(records)
        # print(row_count)


#print(year)

