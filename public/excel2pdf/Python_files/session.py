#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe
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

import datetime
from datetime import date


# tz_IND = pytz.timezone('Asia/Calcutta') 


# import mysql.connector
# from mysql.connector import Error

# try:
#     connection = mysql.connector.connect(host="seqrdoc.com",
# 										database="seqr_demo",
# 										user="developer",
# 										password="developer")
    
        
        #row_count = cursor.rowcount
        #if row_count > 0:
        
        #else:
        #print('No Record')
       #  cursor.execute("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '%s' " % (current_year))
      	# records = cursor.fetchall()  
       #  print(records)

# except Error as e:
#     print("Error while connecting to MySQL", e)
# finally:
#     if connection.is_connected():
#         cursor.close()
#         connection.close()
#         print("MySQL connection is closed")

# try:

# 	connection = mysql.connector.connect(host="seqrdoc.com",
# 										database="seqr_demo",
# 										user="developer",
# 										password="developer")
# 	if connection.is_connected():
# 				db_Info = connection.get_server_info()
		        # cursor = connection.cursor()
		        # cursor.execute("select ep_details, id, extractor_details, template_name, pdf_page, print_bg_file, print_bg_status, verification_bg_file, verification_bg_status from uploaded_pdfs where id = '%s'" % (1))
		        # record = cursor.fetchone() 
		        # print(record)
		    	#print('Testing')
		# cursor = connection.cursor()
  #     	cursor.execute("select * from student_table where publish=1 order by id desc")
  #  	  	records = cursor.fetchall()  
  #  	  	row_count = cursor.rowcount
  #  	  	print(records)
 	 	#connection.commit()

# except Error as e:
#     print("Error while connecting to MySQL", e)
	#
			
	        #db_Info = connection.get_server_info()
	        # cursor = connection.cursor()
	        # cursor.execute("select * from student_table where publish=1 order by id desc")
	       	#  records = cursor.fetchall()  
	       	#  row_count = cursor.rowcount
	       	#  print(records)
#function take input of the datestring like 2017-05-01
def get_financial_year(datestring):
            date = datetime.datetime.strptime(datestring, "%Y-%m-%d").date()
            #initialize the current year
            year_of_date=date.year
            #initialize the current financial year start date
            financial_year_start_date = datetime.datetime.strptime(str(year_of_date)+"-04-01","%Y-%m-%d").date()
            if date<financial_year_start_date:
                    return str(financial_year_start_date.year-1)[2:]+'-'+ str(financial_year_start_date.year)[2:]
            else:
                    return str(financial_year_start_date.year)[2:]+'-'+ str(financial_year_start_date.year+1)[2:]

today = date.today()
print(today)
print(datetime.datetime.now().strftime('%Y%m%d%H%M%S-'))
current_year=get_financial_year(str(today))
print(current_year)
# 	current_year='PN/'+current_year+'/'
# 	if connection.is_connected():
#         cursor = connection.cursor()
# 	    cursor.execute("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '%s'" % (current_year))
# 	    record = cursor.fetchone() 
# 	    print(record[0]+1)
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
#Upload file to secdoc directory
#documents/Single_Sample_PDF/Single_Sample_PDF-20210806200308-30cec2b39231.zip
# URL = "https://demo.seqrdoc.com/admin/store-file"
# pdf_path=pdf_folder+"/"+dt_string+".pdf"
# PARAMS = {'pdf_path':pdf_path,'site_id':5}
# response =requests.get(url = URL, params = PARAMS)
