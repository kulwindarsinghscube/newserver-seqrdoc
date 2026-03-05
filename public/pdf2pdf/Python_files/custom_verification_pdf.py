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

dictionary = {
'demo': 'MONAD_BG.jpg', 
'monad': 'MONAD_BG.jpg',
'bestiu': 'bestiu_pdc_bg.jpg',
'tpsdi': 'Tata_Power_Skill_Institute_final_apprved._20191010105548.jpg'
}

subdomain=sys.argv[1]
template_id=sys.argv[2]
key_id=sys.argv[3] #encrypted key value
bg_image=dictionary[subdomain]
#print(bg_image)
#exit();
serverName="localhost";
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
        cur2=connection.cursor(buffered=True)
        sql_std="SELECT certificate_filename FROM student_table where `key`= '%s'" % (key_id)
        cur2.execute(sql_std)        
        srecord = cur2.fetchone() 
        filename="test_GUID_TRANS_023.pdf" #srecord[0]
except Error as e:
    print("Error while connecting to MySQL", e)

connection.commit()

directory=rootDir+subdomain+"/backend/verification_output" #path to save processed pdf
if not os.path.exists(directory):
    os.makedirs(directory) 
bg_file=rootDir+subdomain+"/backend/canvas/bg_images/" + bg_image
pix = fitz.Pixmap(bg_file) 

#doc = fitz.open(rootDir+subdomain+"/backend/pdf_file/"+filename)
doc = fitz.open(directory+"/"+filename)
page_count=doc.pageCount
print(filename)
for i in doc:
    page = i
    if not(page._isWrapped):
        page._wrapContents()    
    page_dict = page.getText('dict')    
    page_rect=page.MediaBox
    page.insertImage(page_rect, pixmap=pix, overlay=False)
    doc.save(directory+"/1_"+filename, garbage=4, deflate=True, clean=True)
    arg2= directory+"/1_"+filename
    arg1= '-sOutputFile=' +directory+"/withbg"+filename
    proc = subprocess.Popen(['C:/Program Files/gs/gs9.56.1/bin/gswin64c.exe', '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4', '-dPDFSETTINGS=/ebook', '-dNOPAUSE', '-dBATCH',  '-dQUIET', str(arg1), arg2], stdout=subprocess.PIPE)

#print(proc.poll()) # .poll() will return a value once it's complete.
#if proc.poll()== None:
    #time.sleep(1)
    #if os.path.exists(arg2):
        #os.remove(arg2)