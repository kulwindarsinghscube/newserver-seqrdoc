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

tz_IND = pytz.timezone('Asia/Calcutta') 
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
try:
    directory=sys.argv[8]
    rootDir= directory.replace('pdf2pdf', '')
    connection = mysql.connector.connect(host=sys.argv[10],
                                         database=sys.argv[6],
                                         user=sys.argv[11],
                                         password=sys.argv[12])
    if connection.is_connected():
        db_Info = connection.get_server_info()
        cursor = connection.cursor()
        cursor.execute("select ep_details, id, extractor_details, template_name, pdf_page, print_bg_file, print_bg_status, verification_bg_file, verification_bg_status from uploaded_pdfs where id = '%s'" % (sys.argv[1]))
        record = cursor.fetchone()        
        boxes = json.loads(record[0], strict=False)        
        template_id=record[1]
        extractor_details = json.loads(record[2], strict=False)
        template_name=record[3]
        pdf_page=record[4]
        pbg_file=record[5]
        print_bg_status=record[6]
        vbg_file=record[7]
        verification_bg_status=record[8]
        cur=connection.cursor()
        print_setbg=''

        record_unique_id = datetime.datetime.now().strftime('%Y%m%d%H%M%S-') + str(uuid.uuid4()).split('-')[-1]
        sqli="SELECT id FROM file_records ORDER BY id DESC LIMIT 1"
        cur.execute(sqli)
        last_id = cur.fetchone()
        if last_id == None:
            file_records_next_id=1
        else:
            file_records_next_id=last_id[0]+1    
        # get last record's columns
        #for lastID in last_id:
            #print(lastID)     

except Error as e:
    print("Error while connecting to MySQL", e)
"""
finally:
    if (connection.is_connected()):
        #cursor.close()
        #connection.close()
        print("MySQL connection is closed")
"""
#exit()
userid=sys.argv[3]
       
                
def extract_info(extractor_detail,search_value,extra_line_first='',extra_line_second='',page=''):
    for keyval in extractor_detail:
        if search_value.lower() == keyval['name'].lower():
            source_coord = keyval['coords']
            scoord = source_coord.split(",")
            s_rect = fitz.Rect(scoord[0],scoord[1],scoord[2],scoord[3])
            get_text=[]            
            get_text.append(page.getTextbox(s_rect).replace("\n", ""))
            otxt=get_text[0].strip()
            if extra_line_first == '' and extra_line_second == '':
                return otxt +"\n"
            elif extra_line_first != '' and extra_line_second != '':
                return extra_line_first + otxt + extra_line_second +"\n" 
            elif extra_line_first != '' and extra_line_second == '':
                return extra_line_first + otxt +"\n" 
            elif extra_line_first == '' and extra_line_second != '':
                return otxt + extra_line_second +"\n" 


    
def split(word): 
    return [char for char in word]


encrypt_meth = fitz.PDF_ENCRYPT_AES_256  # strongest algorithm
perm = int(
fitz.PDF_PERM_PRINT  # permit printing
)

dirFont = sys.argv[8]+"Python_files/fonts/"
#directory="C:/wamp/www/demo/"
#dirFont = "C:/Program Files/Python38/projects/demo/fonts/"
doc = fitz.open(rootDir+sys.argv[7]+'/uploads/data/'+sys.argv[2])#directory+"uploads/data/"+
page_count=doc.pageCount
arr_content = {} #The array for storing the progress.

get_blank = []
get_extractor_name = []
pno=0
for pagechk in doc:
    pno += 1
    get_extractor_name.append('<br><b>Page '+str(pno)+':</b>')
    for box_chk in extractor_details:
        source_coords_chk = box_chk['coords']
        scoords = source_coords_chk.split(",")
        srect = fitz.Rect(scoords[0],scoords[1],scoords[2],scoords[3])
        blocks = pagechk.getText("blocks", srect)
        blocks.sort(key=lambda block: block[0])  # sort vertically ascending
        try:
            #block_split=str(blocks[0]).split(",")
            block_split=blocks[0]
        except IndexError:
            block_split=''
            extractor_name=box_chk['name']
        
        if block_split=='':
            get_blank.append(extractor_name)
            get_extractor_name.append(extractor_name+', ')

		
if get_blank:
	print("Empty Extractor")
	get_blank_string = ' '.join(get_extractor_name)
	print('Empty Extractor'+get_blank_string) #Blank Source
	exit()

blank_directory=rootDir+sys.argv[7]+'/uploads/blank_pdf/'
if not os.path.exists(blank_directory):
    os.makedirs(blank_directory)
doc_count = fitz.open(rootDir+sys.argv[7]+'/uploads/data/'+sys.argv[2])
pdf_page_count=doc_count.pageCount
page_wh=doc_count[0]
page_width=page_wh.rect.width
page_height=page_wh.rect.height

doc_new = fitz.open()
for row_index in range(0, pdf_page_count):
    doc_new.insertPage(-1, fontsize = '', text = '', fontname = '', width = page_width, height = page_height)
    
doc_new.save(blank_directory+"/"+sys.argv[2], garbage=4, deflate=True) 
 

final_data = {'data':[]}
cnts = 1

pp = pprint.PrettyPrinter(indent=4)
output_file = directory+sys.argv[7]+'/'+"processed_pdfs/"+sys.argv[2]

white = (0, 0, 0, 0)
black = (0, 0, 0, 1)
red = (0, 1, 1, 0)
blue = (1, 1, 0, 0)
pink = (0, 1, 0, 0)
aqua = (1, 0, 0, 0)
green = (1, 0, 1, 0)
purple = (1, 1, 0, 0)
yellow = (0, 0, 1, 0)

#path_pdf_moved= inner_folder+"/" +sys.argv[2]  #
path_pdf_moved= blank_directory+sys.argv[2]
#print(path_pdf_moved)
datetime_IND = datetime.datetime.now(tz_IND) 
beginning_time = datetime_IND.strftime("%H:%M:%S")
start_time = time.time()
data_arr=[]
json_data_list = []

doc_get = fitz.open(rootDir+sys.argv[7]+'/uploads/data/'+sys.argv[2])
for i in doc_get:
    data_arr.append([])    
    page = i
    if not(page._isWrapped):
        page._wrapContents()
    page_data = {cnts:[]}
    words = page.getTextWords()
    page_dict = page.getText('dict')    
    page_rect=page.MediaBox
    #print(page_rect)
    #exit()
    dirName = directory+sys.argv[7]+'/'+"documents/" + str(sys.argv[1])
    #print(dirName)
    for box in boxes:
        data = {}
        otxt = ''
        file_path = ''
        #print()
        if box['source'] == '' or box['source'] == 'Current DateTime':
            srect = fitz.Rect(0,0,0,0)            
        else:
            source_coords = box['source_coords']
            scoords = source_coords.split(",")
            srect = fitz.Rect(scoords[0],scoords[1],scoords[2],scoords[3])        
        
        placer_coords = box['placer_coords']
        pcoords = placer_coords.split(",")
        prect = fitz.Rect(pcoords[0],pcoords[1],pcoords[2],pcoords[3])
        prect_coords = pcoords[0],pcoords[1],pcoords[2],pcoords[3]
        #print(placer_coords)  
        if box['placer_font_name'] == '':  
            placer_font_name = fitz.Font(fontfile=dirFont+"arial.ttf")   
        else:
            placer_font_name = fitz.Font(fontfile=dirFont+box['placer_font_name'])
            
        placer_font_size = box['placer_font_size']                   
        placer_type = box['placer_type']      
        placer_display = box['placer_display']
        if placer_display == '':
            placer_align = int(0)
        else:
            placer_align = int(placer_display)         
            
        if placer_type == 'Invisible':
            placer_color = yellow
        else:
            placer_color = black                
        
        if box['font_color'] == '':
            placer_font_color = black
        else:
            placer_font_color = box['font_color']
            placer_font_color=fitz.utils.getColor(placer_font_color) 
        
        if placer_type == 'Invisible':                        
            invisible_font_color=fitz.utils.getColor("YELLOW")
            inv_txt=''
            get_text=[]
            if box['qr_details']=='':  
                get_text.append(page.getTextbox(srect).replace("\n", ""))
                otxt=get_text[0].strip()
                inv_txt=otxt
            else:
                qr_details = box['qr_details'].replace("{", "").replace("}", "")
                qr_list=list(filter(bool, qr_details.splitlines()))
                for inv in qr_list:
                    str_val = inv.split("^")
                    str_val_count=len(str_val)
                    inv_txt = inv_txt + extract_info(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)
            data['page']=cnts-1
            data['placer_type']='Invisible'
            data['placer_coords']=placer_coords
            data['prect']=prect
            data['txt']=inv_txt
            data['placer_font_name']=box['placer_font_name']
            data['placer_font_size']=placer_font_size
            data['placer_display']=placer_display
            data['font_color']=invisible_font_color
            data['image']=''
            data_arr[cnts-1].append(data)
            #json_data_list.append(data)
        elif placer_type == 'Invisible Image': 
            temp_img = [block for block in page_dict['blocks'] if (fitz.Rect(block['bbox']) in srect and block['type'] == 1)]  
            if len(temp_img) > 0:
                pix = fitz.Pixmap(temp_img[0]['image'])
                if not os.path.exists(dirName):
                    os.makedirs(dirName)
                file_path = dirName + "/"+str(int(round(time.time() * 1000))) + "-" + str(cnts) + "." + temp_img[0]['ext']
                file_path2 = dirName + "/"+str(int(round(time.time() * 1000))) + "-" + str(cnts) + ".png"                        
                pix.writeImage(file_path)
                imgs = Image.open(file_path).convert("L") 
                img1 = ImageOps.colorize(imgs, black ="white", white ="yellow")  
                img1.save(file_path2, 'png')
                
                data['page']=cnts-1                
                data['placer_type']='Invisible Image'
                data['placer_coords']=placer_coords
                data['prect']=prect
                data['txt']=''
                data['placer_font_name']=''
                data['placer_font_size']=''
                data['placer_display']=''
                data['font_color']=''
                data['image'] = file_path2
                data_arr[cnts-1].append(data)
                #json_data_list.append(data);

    cnts += 1
    
#print(data_arr)
data_arr_count=len(data_arr)
#for x in range(len(data_arr)): 
    #print(data_arr[x]) 
    #for y in range(len(data_arr[x])):
        #print(data_arr[x][y])

#for box in data_arr[1]:
    #print(box)
#exit()
cnt=1
doc_invisible = fitz.open(blank_directory+sys.argv[2])
for j in doc_invisible:
    page = j
    if not(page._isWrapped):
        page._wrapContents()
    page_data = {cnt:[]}
    words = page.getTextWords()
    page_dict = page.getText('dict')    
    page_rect=page.MediaBox
    #print(page_rect)
    #exit()
    dirName = directory+sys.argv[7]+'/'+"documents/" + str(sys.argv[1])
    #print(dirName)
    #for box in boxes:
    for box in data_arr[cnt-1]:
        otxt = ''
        file_path = ''        
        
        placer_coords = box['placer_coords']
        pcoords = placer_coords.split(",")
        prect = fitz.Rect(pcoords[0],pcoords[1],pcoords[2],pcoords[3])
        prect_coords = pcoords[0],pcoords[1],pcoords[2],pcoords[3]
        #print(placer_coords)  
        if box['placer_font_name'] == '':  
            placer_font_name = fitz.Font(fontfile=dirFont+"arial.ttf")   
        else:
            placer_font_name = fitz.Font(fontfile=dirFont+box['placer_font_name'])
            
        placer_font_size = box['placer_font_size']                   
        placer_type = box['placer_type']      
        placer_display = box['placer_display']
        if placer_display == '':
            placer_align = int(0)
        else:
            placer_align = int(placer_display)         
            
        if placer_type == 'Invisible':
            placer_color = yellow
        else:
            placer_color = black      
                
        if box['font_color'] == '':
            placer_font_color = black
        else:
            placer_font_color = box['font_color']
            placer_font_color=fitz.utils.getColor(placer_font_color) 
        
        if placer_type == 'Invisible':                        
            invisible_font_color=fitz.utils.getColor("YELLOW")
            otxt=box['txt']
            #print(type(otxt),otxt)
            if type(otxt) is float:      
                page.insertTextbox(prect, otxt, fontfile=str(placer_font_name), fontsize=placer_font_size, align=placer_align, color=invisible_font_color, overlay=True)
            else:
                wr = fitz.TextWriter(page.rect)
                wr.fillTextbox(prect, otxt,  font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                wr.writeText(page, color=invisible_font_color, opacity=float(1.0), overlay=True)             
        elif placer_type == 'Invisible Image': 
            file_path2=box['image']
            page.insertImage(prect, file_path2, overlay=True)              

        #print(dirName);
   
        
    current_date_time=datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S');    

    datetime_IND = datetime.datetime.now(tz_IND)
    ending_time = datetime_IND.strftime("%H:%M:%S")
    end_time=time.time() - start_time
    seconds_to_hhmmss=time.strftime('%H:%M:%S', time.gmtime(end_time))
    page_seconds_to_hhmmss=time.strftime('%H:%M:%S', time.gmtime(end_time/cnt))
    arr_content['percent'] = int(cnt/page_count * 100)
    arr_content['message'] = "Generating "+str(cnt)+"/"+str(page_count)+" PDF(s)"
    arr_content['beginning_time'] = beginning_time
    arr_content['ending_time'] = ending_time
    arr_content['exec_time'] = end_time
    arr_content['hms_time'] = seconds_to_hhmmss
    arr_content['page_time'] = page_seconds_to_hhmmss
    arr_content['pages_processed'] = cnt
    json_object = json.dumps(arr_content, indent = 4)

    f = open(rootDir+sys.argv[7]+'/'+"processed_pdfs/"+sys.argv[5], "w")
    f.write(json_object)
    #time.sleep(1)
    cnt += 1



    connection.commit()
print(path_pdf_moved)

removePath=rootDir+sys.argv[7]+'/'
#print(removePath)
rewisePath = path_pdf_moved.replace(removePath,'')

doc_invisible.save(path_pdf_moved, incremental = True, encryption=0)
#doc.save(path_pdf_moved, garbage=4, deflate=True, owner_pw="owner", encryption=encrypt_meth, permissions=perm)
#doc.save(path_pdf_moved, incremental=True)
#shutil.copyfile(output_file, path_pdf_moved)
print(rewisePath)
#shutil.make_archive(folder+"/"+template_name+"-"+str(record_unique_id), "zip", inner_folder)
#print(folder+"/"+template_name+"-"+str(record_unique_id)+".zip")
total_pages=cnt-1
#print("Total Records:"+str(total_pages))
shutil.rmtree(dirName, ignore_errors=True)
#shutil.rmtree(inner_folder, ignore_errors=True)
#print("documents/"+template_name+"/"+template_name+"-"+str(record_unique_id)+".zip")
#print(pdf_folder+"/"+dt_string+".pdf");
#print(rewisePath)

