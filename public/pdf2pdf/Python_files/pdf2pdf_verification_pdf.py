#!C:/Users/Administrator/AppData/Local/Programs/Python/Python38/python.exe
from __future__ import print_function
import os, PyPDF2
import sys
import fitz
import mysql.connector
from mysql.connector import Error
import subprocess
import shutil
import time

subdomain=sys.argv[1]
template_id=sys.argv[2]
key_id=sys.argv[3] #encrypted key value

serverName="seqrdoc.com";
dbUserName="developer";
dbPassword="developer";
if(subdomain=='demo'):
    dbName = 'seqr_demo'
else:
    dbName = 'seqr_d_'+subdomain

rootDir="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/";
try:
    connection = mysql.connector.connect(host=serverName, database=dbName, user=dbUserName, password=dbPassword)
    if connection.is_connected():
        db_Info = connection.get_server_info()
        cursor = connection.cursor(buffered=True)
        cursor.execute("select verification_bg_file, verification_bg_status from uploaded_pdfs where id = '%s'" % (template_id))
        record = cursor.fetchone()        
        vbg_file=record[0]
        verification_bg_status=record[1]
        cur=connection.cursor(buffered=True)
        verification_setbg=''
        if vbg_file !=0:
            sql_bg="SELECT image_path FROM background_template_master where id= '%s'" % (vbg_file)
            cur.execute(sql_bg)
            count = cur.rowcount
            vrecord = cur.fetchone()            
            if count>0:
                bg_file=rootDir+subdomain+"/backend/canvas/bg_images/" + vrecord[0]
                verification_setbg=verification_bg_status

        cur2=connection.cursor(buffered=True)
        sql_std="SELECT certificate_filename FROM student_table where `key`= '%s'" % (key_id)
        cur2.execute(sql_std)        
        srecord = cur2.fetchone() 
        filename=srecord[0]
except Error as e:
    print("Error while connecting to MySQL", e)

connection.commit()
directory=rootDir+subdomain+"/backend/verification_output" #path to save processed pdf
if not os.path.exists(directory):
    os.makedirs(directory)
#if verification_setbg=='Yes':
    #pix = fitz.Pixmap(bg_file)
"""
doc = fitz.open(rootDir+subdomain+"/backend/pdf_file/"+filename)
page_count=doc.pageCount
#print(filename)
for i in doc:
    page = i
    if not(page._isWrapped):
        page._wrapContents()    
    page_dict = page.getText('dict')    
    page_rect=page.MediaBox
    #if verification_setbg=='Yes':
        #page.insertImage(page_rect, pixmap=pix, overlay=False)
    doc.save(directory+"/1_"+filename, garbage=4, deflate=True, clean=True)
    arg2= directory+"/1_"+filename
    arg1= '-sOutputFile=' +directory+"/2_"+filename
    proc = subprocess.Popen(['C:/Program Files/gs/gs9.56.1/bin/gswin64c.exe', '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4', '-dPDFSETTINGS=/ebook', '-dNOPAUSE', '-dBATCH',  '-dQUIET', str(arg1), arg2], stdout=subprocess.PIPE)
"""
#print(proc.poll()) # .poll() will return a value once it's complete. 
#if proc.poll()== None:
    #time.sleep(1)
    #if os.path.exists(arg2):
        #os.remove(arg2)