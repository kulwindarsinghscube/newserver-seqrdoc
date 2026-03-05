#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe
from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
import errno, os, stat
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
import pyzbar.pyzbar as pyzbar
import re
#from datetime import datetime
import uuid
import pytz
import socket
import requests
import datetime
from datetime import date
from PIL import ImageDraw, ImageFont
import math
#from prompt_toolkit import print_formatted_text, HTML, prompt
#from scipy import ndimage
#import scipy.misc
#import matplotlib.pyplot as plt


tz_IND = pytz.timezone('Asia/Calcutta') 
#sys.argv[1] = template id
#sys.argv[2] = data folder
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
# python directory_extract_and_place_QR.py 2 multi_pages 1 

try:

    directory=sys.argv[8]
    rootDir= directory.replace('/pdf2pdf', '')
    connection = mysql.connector.connect(host=sys.argv[10],
                                         database=sys.argv[6],
                                         user=sys.argv[11],
                                         password=sys.argv[12])
    if connection.is_connected():
        db_Info = connection.get_server_info()
        cursor = connection.cursor()
        cursor.execute("select ep_details,id,extractor_details,template_name,pdf_page, print_bg_file, print_bg_status, verification_bg_file, verification_bg_status, file_name from uploaded_pdfs where id = '%s'" % (sys.argv[1]))
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
        template_file=record[9]
        print_setbg=''
        verification_setbg=''
        cur=connection.cursor()
        if pbg_file !=0 and print_bg_status == 'Yes':
            sql_bg="SELECT image_path FROM background_template_master where id= '%s'" % (pbg_file)
            cur.execute(sql_bg)
            precord = cur.fetchone()
            #print_bg_file=sys.argv[8]+"upload_bgs/"+precord[0] 
            print_bg_file=rootDir+sys.argv[7]+"/backend/canvas/bg_images/" + precord[0]    
            #print_bg_file="C:/wamp/www/demo/upload_bgs/"+precord[0]    
            print_setbg='Yes'
            #print(print_bg_file)
        if vbg_file !=0 and verification_bg_status == 'Yes':
            sql_bg="SELECT image_path FROM background_template_master where id= '%s'" % (vbg_file)
            cur.execute(sql_bg)
            vrecord = cur.fetchone()
            #verification_bg_file=sys.argv[8]+"upload_bgs/"+vrecord[0]
            verification_bg_file=rootDir+sys.argv[7]+"/backend/canvas/bg_images/" + vrecord[0]
            #verification_bg_file="C:/wamp/www/demo/upload_bgs/"+vrecord[0]
            verification_setbg='Yes'
            #print(verification_bg_file)        
        total_length = len(boxes)
        record_unique_id = datetime.datetime.now().strftime('%Y%m%d%H%M%S-') + str(uuid.uuid4()).split('-')[-1]
except Error as e:
    print("Error while connecting to MySQL", e)
"""
finally:
    if (connection.is_connected()):
        #cursor.close()
        #connection.close()
        print("MySQL connection is closed")
"""
userid=sys.argv[3]
#result = re.search('"page_no":(.*),"placer":', record[0])
#print(result.group(1))
#exit()

def decode(im, my_pageno):
    # Find barcodes and QR codes
    decodedObjects = pyzbar.decode(im)
    for obj in decodedObjects:
        #print('Type : ', obj.type)
        #print(str(my_pageno)+'\n'+obj.data.decode('utf-8'))
        if my_pageno==1:
            my_qr_string=str(my_pageno)+'\n'+obj.data.decode('utf-8')
        else:
            fullstring=obj.data.decode('utf-8')
            #my_qr_string = str(my_pageno)+'\n'.join(fullstring.split('\n')[1:])
            my_qr_string=str(my_pageno)+'\n'+fullstring.partition("\n")[2] #Remove first line (page number)

    return my_qr_string

def extract_microline_info(extractor_detail,search_value,extra_line_first='',extra_line_second='',page=''):
    for keyval in extractor_detail:
        if search_value.lower() == keyval['name'].lower():
            source_coord = keyval['coords']
            scoord = source_coord.split(",")
            s_rect = fitz.Rect(scoord[0],scoord[1],scoord[2],scoord[3])  
            get_text=[]            
            get_text.append(page.getTextbox(s_rect).replace("\n", ""))
            otxt=get_text[0].replace(" ", "")    
            if extra_line_first == '' and extra_line_second == '':
                return otxt
            elif extra_line_first != '' and extra_line_second != '':
                return extra_line_first + otxt + extra_line_second 
            elif extra_line_first != '' and extra_line_second == '':
                return extra_line_first + otxt 
            elif extra_line_first == '' and extra_line_second != '':
                return otxt + extra_line_second

def extract_plainText(extractor_detail,search_value,extra_line_first='',extra_line_second='',page=''):
    for keyval in extractor_detail:
        if search_value.lower() == keyval['name'].lower():
            source_coord = keyval['coords']
            scoord = source_coord.split(",")
            s_rect = fitz.Rect(scoord[0],scoord[1],scoord[2],scoord[3])  
            get_text=[]            
            get_text.append(page.getTextbox(s_rect).replace("\n", ""))
            otxt=get_text[0].strip()
            if extra_line_first == '' and extra_line_second == '':
                return otxt +" "
            elif extra_line_first != '' and extra_line_second != '':
                return extra_line_first + otxt + extra_line_second +" " 
            elif extra_line_first != '' and extra_line_second == '':
                return extra_line_first + otxt +" " 
            elif extra_line_first == '' and extra_line_second != '':
                return otxt + extra_line_second +" " 
                
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

def CreateGhostImage(dirName, name, p_font_size, ghost_width, ghost_height):
    dirChars = sys.argv[8]+"Python_files/chars/"+str(p_font_size)
    #dirChars = "C:/Program Files/Python38/projects/demo/chars/"+str(p_font_size)
    name=name.upper()    
    single_char=split(name)       
    my_list = list()
    for c in single_char:
        my_list.append(dirChars +"/"+ c +".png")    
    print(my_list)
    images = [Image.open(x) for x in my_list]
    widths, heights = zip(*(i.size for i in images))
    total_width = sum(widths)
    max_height = max(heights)    
    new_im = Image.new('RGB', (total_width, max_height))
    x_offset = 0
    for im in images:
      new_im.paste(im, (x_offset,0))
      x_offset += im.size[0]

    new_im.save(dirName +"/"+ name +".png")  
    isize=ghost_width,ghost_height            
    im = Image.open(dirName +"/"+ name +".png")
    im.thumbnail((isize), Image.ANTIALIAS)
    im.save(dirName +"/"+ name +str(p_font_size)+"_th.png", quality=100)    
    #return dirName +"/"+ name +".png" 
    return dirName +"/"+ name +str(p_font_size)+"_th.png"
    
def split(word): 
    return [char for char in word]

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

dirFont = sys.argv[8]+"Python_files/fonts/"
white = (0, 0, 0, 0)
black = (0, 0, 0, 1)
red = (0, 1, 1, 0)
blue = (1, 1, 0, 0)
pink = (0, 1, 0, 0)
aqua = (1, 0, 0, 0)
green = (1, 0, 1, 0)
purple = (1, 1, 0, 0)
yellow = (0, 0, 1, 0)
#cl = fitz.utils.getColorInfoList()
#print(cl)
#exit() ghostwhite
watermark_text_color=fitz.utils.getColor('gray')
temp_file = template_file.split("/")   
#Get all the PDF filenames
pdffiles = []
pdffiles.append(temp_file[2])
page_counts=len(pdffiles)

preview_folder = rootDir+sys.argv[7]+"/processed_pdfs/preview"
if not os.path.exists(preview_folder):
	os.makedirs(preview_folder)

cnt = 1
encrypt_meth = fitz.PDF_ENCRYPT_AES_256  # strongest algorithm
perm = int(
fitz.PDF_PERM_PRINT  # permit printing
)

for file_name in pdffiles:
    doc = fitz.open(rootDir+sys.argv[7]+"/uploads/pdfs/"+file_name)
    page_count=doc.pageCount-1    
    arr_content = {} #The array for storing the progress.
    datetime_IND = datetime.datetime.now(tz_IND) 
    beginning_time = datetime_IND.strftime("%H:%M:%S")
    start_time = time.time()    
    final_data = {'data':[]}    
    pp = pprint.PrettyPrinter(indent=4)
    print(file_name)
    output_file = preview_folder+"/"+file_name
     
    #Start save first page QR details and position
    qr_pt_type=''
    watermark_check=''
    static_text_check=''
    for pageName in doc:
        pageNo=pageName.number        
        word_s = pageName.getTextWords()
        dir_Name = rootDir+sys.argv[7]+'/'+"documents/" + str(sys.argv[1])        
        for bocs in boxes:           
            if pageNo == bocs["page_no"]:
                if bocs['source'] == '' or bocs['source'] == 'Current DateTime':
                    s_rect = fitz.Rect(0,0,0,0)            
                else:
                    s_coords = bocs['source_coords'].split(",")
                    s_rect = fitz.Rect(s_coords[0],s_coords[1],s_coords[2],s_coords[3])    
                
                p_coords = bocs['placer_coords'].split(",")
                p_rect = fitz.Rect(p_coords[0],p_coords[1],p_coords[2],p_coords[3])
                
                if pageNo == 0 and bocs['placer_type'] == 'Common Static Text':
                    static_text_check="On Each Page"
                    static_prect=p_rect
                    if bocs['placer_font_name'] == '':  
                        st_placer_font_name = fitz.Font(fontfile=dirFont+"arial.ttf")   
                    else:
                        st_placer_font_name = fitz.Font(fontfile=dirFont+bocs['placer_font_name'])
                       
                    st_placer_font_size = bocs['placer_font_size']
                    st_placer_font_underline = bocs['placer_font_underline'] 
                    st_placer_align = bocs['placer_display'] 
                    st_placer_opacity = bocs['opacity_val'] 
                    st_placer_degree_angle = bocs['degree_angle']
                    if bocs['font_color'] == '':
                        st_placer_font_color = black
                    else:
                        st_placer_font_color = bocs['font_color']
                        st_placer_font_color=fitz.utils.getColor(st_placer_font_color) 
                    static_details = bocs['qr_details']
                if pageNo == 0 and bocs['placer_type'] == 'Watermark Multi Lines':
                    watermark_check=bocs["qr_position"]
                    if bocs['placer_font_name'] == '':  
                        wm_placer_font_name = fitz.Font(fontfile=dirFont+"arial.ttf")   
                    else:
                        wm_placer_font_name = fitz.Font(fontfile=dirFont+bocs['placer_font_name'])
                    wm_placer_font_size = bocs['placer_font_size']
                    wm_placer_align = bocs['placer_display'] 
                    wm_placer_opacity = bocs['opacity_val'] 
                    if bocs['line_height'] == '':
                        wm_placer_lineHeight = 1
                    else:
                        wm_placer_lineHeight = bocs['line_height'] 
                    
                    wm_placer_degree_angle = bocs['degree_angle']
                    if bocs['font_color'] == '':
                        wm_placer_font_color = black
                    else:
                        wm_placer_font_color = bocs['font_color']
                        wm_placer_font_color=fitz.utils.getColor(wm_placer_font_color)                         
                    
                    wm_txt=''
                    if bocs['qr_details']=='':                        
                        wm_get_text=[]
                        wm_get_text.append(pageName.getTextbox(s_rect).replace("\n", ""))
                        temp = wm_get_text[0].strip()+' '   
                        #chrPerLine=int(float(p_rect.width))
                        #chrPerLine=int(float(pageName.rect.width))+int(float(pageName.rect.height))
                        chrPerLine=int(390)
                        Totalchrs=str(chrPerLine).split(".")[0]
                        wm_repeat_txt=temp * int(Totalchrs)                       
                    else:
                        wm_details = bocs['qr_details'].replace("{", "").replace("}", "")
                        wm_list=list(filter(bool, wm_details.splitlines()))
                        for inv in wm_list:
                            str_val = inv.split("^")
                            str_val_count=len(str_val)
                            wm_txt = wm_txt + extract_plainText(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),pageName)                 
                        #print(wm_txt)   
                        #chrPerLine=int(float(pageName.rect.width))+int(float(pageName.rect.height))
                        # areaPage=int(float(p_rect.width)*int(float(p_rect.height)))
                        # font = ImageFont.truetype('arial.ttf', wm_placer_font_size)
                        # size = font.getsize(wm_txt)
                        # mmWidth= int(math.ceil(float(size[0])*float(0.26458333))) 
                        # charPerSingleLine=int(math.ceil(364/mmWidth))
                        # areaPerLine=int(mmWidth)*int(charPerSingleLine)*int(size[1])
                        # totalRepeatText=int(math.ceil(areaPage/areaPerLine))
                        chrPerLine=int(390)
                        Totalchrs=str(chrPerLine).split(".")[0]
                        Totalchrs=int(Totalchrs) * float(1.69) 
                        wm_repeat_txt=wm_txt * int(Totalchrs)
                        #wm_repeat_txt=wm_repeat_txt * int(2)
                        # no_of_repeat=int(totalRepeatText) * int(charPerSingleLine)
                        # no_of_repeat= int(no_of_repeat) + int(10) 
                         #wm_repeat_txt=wm_txt * int(no_of_repeat)                   
                
                if pageNo == 0 and (bocs['placer_type'] == 'QR Plain Text' or bocs['placer_type'] == 'QR Invisible Plain Text'):
                    if bocs['placer_font_name'] == '':  
                        qr_placer_font_name = fitz.Font(fontfile=dirFont+"arial.ttf")   
                    else:
                        qr_placer_font_name = fitz.Font(fontfile=dirFont+bocs['placer_font_name'])
                    qr_placer_font_size = bocs['placer_font_size'] 
                    if bocs['placer_type'] == 'QR Invisible Plain Text':
                        qr_placer_color = yellow
                    else:
                        qr_placer_color = black
                    qr_placer_display = bocs['placer_display']
                    if qr_placer_display == '':
                        qr_placer_align = int(0)
                    else:
                        qr_placer_align = int(qr_placer_display) 
                    
                    qrp_txt=''
                    qrp_get_text=[]                 
                    
                    if bocs['qr_details']=='':                
                        qrp_get_text.append(pageName.getTextbox(s_rect).replace("\n", ""))
                        qrp_txt_string = qrp_get_text[0].strip()
                        qr_pt_type="single"
                        qr_pt_prect = p_rect
                    else:
                        qrp_details = bocs['qr_details'].replace("{", "").replace("}", "")
                        qrp_list=list(filter(bool, qrp_details.splitlines()))
                        qr_pt_type="combination"    
                        qr_pt_prect = p_rect
                        for qrp_inv in qrp_list:
                            qrp_str_val = qrp_inv.split("^")
                            str_val_count=len(str_val)
                            qrp_txt = qrp_txt + extract_plainText(extractor_details,qrp_str_val[1],str(qrp_str_val[0]),str(qrp_str_val[2]),pageName)
                        
                        qrp_txt = qrp_txt              

                if pageNo == 0 and (bocs['placer_type'] == 'QR Default' or bocs['placer_type'] == 'QR Dynamic'):
                    #print(bocs['source'],"|",bocs['page_no'])
                    qr_position_check=bocs["qr_position"]         
                    if bocs['placer_type'] == 'QR Default': 
                        if not os.path.exists(dir_Name):
                            os.makedirs(dir_Name) 
                        now = datetime.datetime.now()            
                        #dtstring = now.strftime("%Y%m%d%H%M%S")+str(pageNo)
                        dtstring = now.strftime("%Y%m%d%H%M%S") + str(uuid.uuid4()).split('-')[-1]
                        hash_result = hashlib.md5(dtstring.encode()) 
                        barcode_encode=hash_result.hexdigest()           
                        qr_text=barcode_encode
                        qrc = qrcode.QRCode(
                            version=1,
                            error_correction=qrcode.constants.ERROR_CORRECT_L,
                            box_size=4,
                            border=0,
                        )
                        qrc.add_data(qr_text)
                        qrc.make(fit=True)
                        img = qrc.make_image()  # fill_color="black", back_color="white"
                        img.save(dir_Name+"/"+"qr_"+str(barcode_encode)+".png")
                        qrcodeFile=dir_Name+"/"+"qr_"+str(barcode_encode)+".png"  
                        qr_prect = p_rect
                    elif bocs['placer_type'] == 'QR Dynamic': 
                        if not os.path.exists(dir_Name):
                            os.makedirs(dir_Name) 
                        qr_text=''
                        now = datetime.datetime.now()   
                        get_text=[]
                        if bocs['source'] == '' or bocs['source'] == 'Current DateTime':
                            #dtstring = now.strftime("%Y%m%d%H%M%S")+str(pageNo)
                            dtstring = now.strftime("%Y%m%d%H%M%S") + str(uuid.uuid4()).split('-')[-1]
                            hash_result = hashlib.md5(dtstring.encode()) 
                            barcode_encode=hash_result.hexdigest()
                        else:
                            get_text.append(pageName.getTextbox(s_rect).replace("\n", ""))
                            dtstring = get_text[0].replace("/", "").replace("\\'", "").replace("-", "").strip()                        
                            hash_result = hashlib.md5(dtstring.encode()) 
                            barcode_encode=hash_result.hexdigest()         
                            
                        qr_detail = bocs['qr_details'].replace("{", "").replace("}", "")
                        qrc_list=list(filter(bool, qr_detail.splitlines()))
                        for n in qrc_list:
                            str_val = n.split("^")
                            str_val_count=len(str_val)
                            qr_text = qr_text + extract_info(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),pageName)
                        qr_text =  qr_text + "\n"+barcode_encode
                        #print("QR:",qr_text)                        
                        qrc = qrcode.QRCode(
                            version=1,
                            error_correction=qrcode.constants.ERROR_CORRECT_L,
                            box_size=4,
                            border=0,
                        )
                        qrc.add_data(qr_text)
                        qrc.make(fit=True)
                        img = qrc.make_image()  # fill_color="black", back_color="white"
                        img.save(dir_Name+"/"+"qr_"+str(barcode_encode)+".png")
                        qrcodeFile=dir_Name+"/"+"qr_"+str(barcode_encode)+".png"  
                        qr_prect = p_rect
    #End save first page QR details and position
    
    for i in doc:        
        page = i        
        if not(page._isWrapped):
            page._wrapContents()
        page_no=page.number  
        PageCount=doc.pageCount
        old_rotation = page.rotation
        #print(PageCount)
        #print(page_no)
        page_data = {cnt:[]}
        words = page.getTextWords()
        page_dict = page.getText('dict')
        page_rect = page.MediaBox
        #print(page_dict)
        dirName = rootDir+sys.argv[7]+"/processed_pdfs/preview/" + str(sys.argv[1])     
        #Start - Place QR Code on all pages 
        if qr_position_check == "On Each Page":
            #if old_rotation:
                #page.setRotation(old_rotation)
            my_pageno=int(page_no)+int(1)
            # QR image            
            img_qrcodeFile = Image.open(qrcodeFile)
            # insert page no in QR code
            qr_edited_txt=decode(img_qrcodeFile,my_pageno)
            qrc = qrcode.QRCode(
                version=1,
                error_correction=qrcode.constants.ERROR_CORRECT_L,
                box_size=4,
                border=0,
            )
            qrc.add_data(qr_edited_txt)
            #print(qr_edited_txt)
            qrc.make(fit=True)
            img = qrc.make_image()  # fill_color="black", back_color="white"
            img.save(dir_Name+"/"+"qr_"+str(barcode_encode)+".png")
            qrcodeFile=dir_Name+"/"+"qr_"+str(barcode_encode)+".png" 
            #page._wrapContents()
            page.insertImage(qr_prect, qrcodeFile, overlay=True)
            if my_pageno==1:
                add_qr_edited_txt=qr_edited_txt
            # QR Plain Text 
            if qr_pt_type == "single":
                #page.insertTextbox(qr_pt_prect, qrp_txt_string, fontsize=qr_placer_font_size, color=qr_placer_color, align = qr_placer_align, overlay=True)
                wr = fitz.TextWriter(page.rect)
                wr.fillTextbox(qr_pt_prect, qrp_txt_string, font=qr_placer_font_name, fontsize=qr_placer_font_size, align=qr_placer_align)
                wr.writeText(page, color=qr_placer_color, opacity=float(1.0), overlay=True)   
            elif qr_pt_type == "combination":                        
                #page.insertTextbox(qr_pt_prect, qrp_txt, fontsize=qr_placer_font_size, color=qr_placer_color, align = qr_placer_align, overlay=True)
                wr = fitz.TextWriter(page.rect)
                wr.fillTextbox(qr_pt_prect, qrp_txt, font=qr_placer_font_name, fontsize=qr_placer_font_size, align=qr_placer_align)
                wr.writeText(page, color=qr_placer_color, opacity=float(1.0), overlay=True) 
         
        #End - Place QR Code on all pages
        for box in boxes:            
            if page_no == box["page_no"]:                
                temp = ''
                otxt = ''
                file_path = ''
                #print(box['placer_type']+" | "+box['source'])
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
                if box['placer_font_name'] == '':  
                    placer_font_name = fitz.Font(fontfile=dirFont+"arial.ttf")   
                else:
                    placer_font_name = fitz.Font(fontfile=dirFont+box['placer_font_name'])

                placer_font_underline = box['placer_font_underline']      
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
                
                placer_degree_angle = box['degree_angle']
                if placer_degree_angle == '':
                    placer_degree_angle = int(0)
                else:
                    placer_degree_angle = int(placer_degree_angle)

                placer_opacity = box['opacity_val']
                if box['line_height'] == '':
                    placer_lineHeight = 1
                else:
                    placer_lineHeight = box['line_height'] 
                
                if box['font_color'] == '':
                    placer_font_color = black
                else:
                    placer_font_color = box['font_color']
                    placer_font_color=fitz.utils.getColor(placer_font_color)                 
                
                #Start - Place QR Code on first page
                if qr_position_check == "On First Page":
                    # QR on first page
                    if placer_type == 'QR Default': 
                        if not os.path.exists(dirName):
                            os.makedirs(dirName) 
                        now = datetime.datetime.now()            
                        #dt_string = now.strftime("%Y-%m-%d %H:%M:%S")+str(cnt)
                        dt_string = now.strftime("%Y%m%d%H%M%S")+str(cnt)
                        result = hashlib.md5(dt_string.encode()) 
                        barcode_en=result.hexdigest()           
                        qr_txt=barcode_en
                        qr = qrcode.QRCode(
                            version=1,
                            error_correction=qrcode.constants.ERROR_CORRECT_L,
                            box_size=4,
                            border=0,
                        )
                        qr.add_data(qr_txt)
                        qr.make(fit=True)
                        img = qr.make_image()  # fill_color="black", back_color="white"
                        img.save(dirName+"/"+"qr_"+str(barcode_en)+".png")
                        qrcode_file=dirName+"/"+"qr_"+str(barcode_en)+".png"            
                        page.insertImage(prect, qrcode_file, overlay=True)    
                    elif placer_type == 'QR Dynamic': 
                        if not os.path.exists(dirName):
                            os.makedirs(dirName) 
                        qr_txt=''
                        now = datetime.datetime.now()   
                        get_text=[]
                        if box['source'] == '' or box['source'] == 'Current DateTime':
                            dt_string = now.strftime("%Y%m%d%H%M%S")+str(cnt)
                            result = hashlib.md5(dt_string.encode()) 
                            barcode_en=result.hexdigest()
                        else:
                            #dt_string = otxt.replace("/", "")
                            get_text.append(page.getTextbox(srect).replace("\n", ""))
                            dt_string = get_text[0].replace("/", "").replace("\\", "").replace("-", "").strip()                        
                            result = hashlib.md5(dt_string.encode()) 
                            barcode_en=result.hexdigest()         
                            
                        qr_details = box['qr_details'].replace("{", "").replace("}", "")
                        qr_list=list(filter(bool, qr_details.splitlines()))
                        for n in qr_list:
                            str_val = n.split("^")
                            str_val_count=len(str_val)
                            qr_txt = qr_txt + extract_info(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)
                        qr_txt =  qr_txt + "\n"+barcode_en
                        
                        qr = qrcode.QRCode(
                            version=1,
                            error_correction=qrcode.constants.ERROR_CORRECT_L,
                            box_size=4,
                            border=0,
                        )
                        qr.add_data(qr_txt)
                        qr.make(fit=True)
                        img = qr.make_image()  # fill_color="black", back_color="white"
                        img.save(dirName+"/"+"qr_"+str(barcode_en)+".png")
                        qrcode_file=dirName+"/"+"qr_"+str(barcode_en)+".png"            
                        page.insertImage(prect, qrcode_file, overlay=True)
                #End - Place QR Code on first page 
                
                if placer_type == 'Barcode': 
                    get_text=[]
                    get_text.append(page.getTextbox(srect).replace("\n", ""))
                    bdt_string = get_text[0].strip()
                    EAN = barcode.get_barcode_class('code128')
                    ean = EAN(bdt_string, writer=ImageWriter())
                    options = {
                        'dpi': 200,
                        'write_text': False,
                        'module_width': 5,
                        'module_height': 100,
                        'quiet_zone': 0,
                        'text_distance': 0
                    }            
                    barcode_file = ean.save(dirName, options = options)               
                    page.insertImage(prect, barcode_file, overlay=True)                   
                elif placer_type == 'Micro Line': 
                    mi_txt=''
                    if box['qr_details']=='':
                        get_text=[]
                        get_text.append(page.getTextbox(srect).replace("\n", ""))
                        temp = get_text[0].replace(" ", "").strip()   
                        text_len=len(temp)
                        if isinstance(placer_font_size, float)==True:
                            textwidth = fitz.getTextlength(temp, fontsize=float(placer_font_size))    
                        else:
                            textwidth = fitz.getTextlength(temp, fontsize=int(placer_font_size))   
                                    
                        #print(textwidth)
                        chrPerLine=int(float(prect.width))/textwidth
                        Totalchrs=str(chrPerLine).split(".")[0]
                        #print ("chr = ", chrPerLine)
                        #print (Totalchrs)
                        repeat_txt=temp * int(Totalchrs)
                        if isinstance(placer_font_size, float)==True:
                            repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=float(placer_font_size))    
                        else:
                            repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=int(placer_font_size))   
                        
                        #print(int(float(prect.width)),"|",int(textwidth),"|",int(float(repeat_textwidth)))
                        remain_space=int(float(prect.width))-int(float(repeat_textwidth))-3        
                        #print("rs: ",remain_space,"|",text_len)
                        if remain_space>0:                
                            RchrPerLine=int(remain_space)/textwidth
                            remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
                            remain_chrs = temp[0:int(remain_chrs_count)]
                        else:
                            remain_chrs = ''
                            
                        #print(remain_chrs) # 0 = left, 1 = center, 2 = right
                        #page.insertTextbox(prect, (temp * int(Totalchrs))+remain_chrs, fontsize=placer_font_size, align=placer_align, color=placer_font_color, overlay=True)
                        wr = fitz.TextWriter(page.rect)
                        wr.fillTextbox(prect, (temp * int(Totalchrs))+remain_chrs, fontsize=placer_font_size, align=placer_align)
                        wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True) 
                    else:
                        qr_details = box['qr_details'].replace("{", "").replace("}", "")
                        qr_list=list(filter(bool, qr_details.splitlines()))
                        for inv in qr_list:
                            str_val = inv.split("^")
                            str_val_count=len(str_val)
                            mi_txt = mi_txt + extract_microline_info(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)                    
                        #print(mi_txt)   
                        mi_txt = mi_txt.replace(" ", "")
                        text_len=len(mi_txt)    
                        if isinstance(placer_font_size, float)==True:
                            textwidth = fitz.getTextlength(mi_txt, fontsize=float(placer_font_size))    
                        else:
                            textwidth = fitz.getTextlength(mi_txt, fontsize=int(placer_font_size))   
                                    
                        chrPerLine=int(float(prect.width))/textwidth
                        Totalchrs=str(chrPerLine).split(".")[0]
                        repeat_txt=mi_txt * int(Totalchrs)
                        if isinstance(placer_font_size, float)==True:
                            repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=float(placer_font_size))    
                        else:
                            repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=int(placer_font_size))   
                        
                        remain_space=int(float(prect.width))-int(float(repeat_textwidth))-3        
                        if remain_space>0:                
                            RchrPerLine=int(remain_space)/textwidth
                            remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
                            remain_chrs = mi_txt[0:int(remain_chrs_count)]
                        else:
                            remain_chrs = ''
                            
                        #page.insertTextbox(prect, (mi_txt * int(Totalchrs))+remain_chrs, fontsize=placer_font_size, align=placer_align, color=placer_font_color, overlay=True)
                        wr = fitz.TextWriter(page.rect)
                        wr.fillTextbox(prect, (mi_txt * int(Totalchrs))+remain_chrs, fontsize=placer_font_size, align=placer_align)
                        wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True)
                elif placer_type == 'Invisible': 
                    invisible_font_color=fitz.utils.getColor("YELLOW")
                    inv_txt=''
                    get_text=[]
                    if box['qr_details']=='':
                        get_text.append(page.getTextbox(srect).replace("\n", ""))
                        otxt=get_text[0].strip()                        
                        #page.insertTextbox(prect, otxt, fontsize=placer_font_size, color=placer_color, align = placer_align, overlay=True)
                        wr = fitz.TextWriter(page.rect)
                        wr.fillTextbox(prect, otxt, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                        #wr.writeText(page, color=placer_color, opacity=float(1.0), overlay=True)
                        wr.writeText(page, color=invisible_font_color, opacity=float(1.0), overlay=True)                         
                    else:
                        qr_details = box['qr_details'].replace("{", "").replace("}", "")
                        qr_list=list(filter(bool, qr_details.splitlines()))
                        for inv in qr_list:
                            str_val = inv.split("^")
                            str_val_count=len(str_val)
                            inv_txt = inv_txt + extract_info(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)                          
                             
                        #page.insertTextbox(prect, inv_txt, fontsize=placer_font_size, color=placer_color, align = placer_align, overlay=True)
                        wr = fitz.TextWriter(page.rect)
                        wr.fillTextbox(prect, inv_txt, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                        #wr.writeText(page, color=placer_color, opacity=float(1.0), overlay=True)
                        wr.writeText(page, color=invisible_font_color, opacity=float(1.0), overlay=True)  
                elif placer_type == 'Invisible Image': 
                    temp_img = [block for block in page_dict['blocks'] if (fitz.Rect(block['bbox']) in srect and block['type'] == 1)]  
                    if len(temp_img) > 0:
                        pix = fitz.Pixmap(temp_img[0]['image'])
                        if not os.path.exists(dirName):
                            os.makedirs(dirName)
                        file_path = dirName + "/"+str(int(round(time.time() * 1000))) + "-" + str(cnt) + "." + temp_img[0]['ext']
                        file_path2 = dirName + "/"+str(int(round(time.time() * 1000))) + "-" + str(cnt) + ".png"                        
                        pix.writeImage(file_path)
                        imgs = Image.open(file_path).convert("L") 
                        img1 = ImageOps.colorize(imgs, black ="white", white ="yellow")             
                        img1.save(file_path2, 'png')
                        page.insertImage(prect, file_path2, overlay=True)                     
                elif placer_type == 'Ghost Image': 
                    if placer_degree_angle==90:
                        half_width=int(float(prect.height))/2
                        top_pos_minus=(int(float(pcoords[0])) - int(float(half_width)))  
                        add_right_pos=int(float(pcoords[0])) - top_pos_minus  
                        left_pos=int(float(pcoords[0]))-add_right_pos
                        top_pos=int(float(pcoords[1]))
                        right_pos=int(float(pcoords[2])) + add_right_pos
                        bottom_pos=int(float(pcoords[3]))               
                        prects = fitz.Rect(left_pos,top_pos,right_pos,bottom_pos)       
                    else:
                        prects=prect
                    
                    get_text=[]
                    get_text.append(page.getTextbox(srect).replace("\n", ""))
                    temp = ''.join(c for c in get_text[0].strip().replace("/", "").replace(".", "") if c.isalnum())
                    ghost_width = round(box['width'] * 3.7795275591)  # Millimeter to Pixel, 1 mm = 3.7795275591 pixel
                    ghost_height = round(box['height'] * 3.7795275591)  
                    ghost_words = box['ghost_words']  
                    PrintableChars=temp[ 0 : ghost_words ] #extract first chars
                    if not os.path.exists(dirName):
                        os.makedirs(dirName)            
                    ghostImg=CreateGhostImage(dirName, PrintableChars, placer_font_size, ghost_width, ghost_height)
                    page.insertImage(prects, ghostImg,overlay=True, keep_proportion=True, rotate=placer_degree_angle)
                elif placer_type == 'Image':
                    #image_path=rootDir+sys.argv[7]+"/upload_images/" + box['image_path']
                    image_path=rootDir+sys.argv[7]+"/backend/templates/pdf2pdf_images/" + box['image_path']
                    page.insertImage(prect, image_path,overlay=True)
                elif placer_type == 'Plain Text':
                    inv_txt=''
                    get_text=[]
                    if box['qr_details']=='':                
                        get_text.append(page.getTextbox(srect).replace("\n", ""))
                        otxt_string = get_text[0].strip()
                        #otxt_string = otxt.replace("/", "")
                        #page.insertTextbox(prect, otxt_string, fontsize=placer_font_size, color=placer_color, align = placer_align, overlay=True)
                        wr = fitz.TextWriter(page.rect)
                        wr.fillTextbox(prect, otxt_string, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                        wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True)                          
                    else:
                        qr_details = box['qr_details'].replace("{", "").replace("}", "")
                        qr_list=list(filter(bool, qr_details.splitlines()))
                        for inv in qr_list:
                            str_val = inv.split("^")
                            str_val_count=len(str_val)
                            inv_txt = inv_txt + extract_plainText(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)              
                            
                        #page.insertTextbox(prect, inv_txt, fontsize=placer_font_size, color=placer_color, align = placer_align, overlay=True)
                        wr = fitz.TextWriter(page.rect)
                        wr.fillTextbox(prect, inv_txt, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                        wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True) 
                elif placer_type == 'Static Text':                       
                    otxt_string = box['qr_details']
                    #page.addUnderlineAnnot(prect)                    
                    m = fitz.Matrix(0) #placer_degree_angle
                    points = fitz.Point(prect.x0, prect.y0)
                    wr = fitz.TextWriter(page.rect)
                    wr.fillTextbox(prect, otxt_string, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                    wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=True, morph=(points,m))
                    #textbox = wr.textRect & prect  # calculate rect intersection
                    
                    if placer_font_underline=="underline":
                        rl = page.searchFor(otxt_string)  
                        #print(rl)#[Rect(118.0, 84.5999984741211, 286.0600280761719, 106.80000305175781)]
                        output = str(rl[0]).replace("Rect", "").replace("(", "").replace(")", "")                    
                        ucoords = output.split(",")
                        urect=fitz.Rect(ucoords[0],ucoords[1],ucoords[2],ucoords[3])
                        shape = page.newShape()
                        shape.drawLine(urect.bl, urect.br)
                        #shape.drawLine(textbox.bl, textbox.br)
                        shape.finish(color=placer_font_color, stroke_opacity=float(placer_opacity))
                        shape.commit()
                        """
                        rl = page.searchFor(otxt_string, quads=True)
                        annot = page.addUnderlineAnnot(rl[0])    
                        annot.setBorder(border=None, width=0.3, style=None, dashes=None)
                        annot.setColors(colors=placer_font_color, stroke=placer_font_color)
                        """
                elif placer_type == 'Watermark Text':
                    inv_txt=''
                    get_text=[]
                    if box['qr_details']=='':                
                        get_text.append(page.getTextbox(srect).replace("\n", ""))
                        otxt_string = get_text[0].strip()
                        #page.insertTextbox(prect, otxt_string, fontsize=placer_font_size, color=watermark_text_color, align = placer_align, overlay=False)
                        m = fitz.Matrix(placer_degree_angle)
                        ir = fitz.IRect(prect_coords)
                        prect_new=ir * m         
                        wr = fitz.TextWriter(page.rect)
                        points = fitz.Point(prect.x0, prect.y0)  
                        #prect.x1=prect.x1+int(100) 
                        #prect.y1=prect.y1+int(100)                
                        wr.fillTextbox(prect, otxt_string, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                        wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=True, morph=(points,m))
                    else:
                        qr_details = box['qr_details'].replace("{", "").replace("}", "")
                        qr_list=list(filter(bool, qr_details.splitlines()))
                        for inv in qr_list:
                            str_val = inv.split("^")
                            str_val_count=len(str_val)
                            inv_txt = inv_txt + extract_plainText(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)
                            
                        #page.insertTextbox(prect, inv_txt, fontsize=placer_font_size, color=watermark_text_color, align = placer_align, overlay=False)
                        m = fitz.Matrix(placer_degree_angle)
                        ir = fitz.IRect(prect_coords) 
                        prect_new=ir * m         
                        wr = fitz.TextWriter(page.rect)
                        points = fitz.Point(prect.x0, prect.y0)  
                        #prect.x1=prect.x1+int(150) 
                        #prect.y1=prect.y1+int(150)     
                        wr.fillTextbox(prect, inv_txt, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                        wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=True, morph=(points,m))
                
                if watermark_check == "On First Page":
                    if placer_type == 'Watermark Multi Lines': 
                        mi_txt=''
                        if box['qr_details']=='':
                            get_text=[]
                            get_text.append(page.getTextbox(srect).replace("\n", ""))
                            temp = get_text[0].strip()+' '   
                            text_len=len(temp)
                            #chrPerLine=int(float(page.rect.width))+int(float(page.rect.height))
                            chrPerLine=int(390)
                            Totalchrs=str(chrPerLine).split(".")[0]
                            repeat_txt=temp * int(Totalchrs)   
                            m = fitz.Matrix(placer_degree_angle)
                            ir = fitz.IRect(page_rect)
                            new_page_rect=ir * m  
                            new_page_rect.x1=new_page_rect.x1+int(150) 
                            new_page_rect.y1=new_page_rect.y1+int(200)        
                            wr = fitz.TextWriter(page.rect)
                            points = fitz.Point(0, 0)
                            wr.fillTextbox(new_page_rect, repeat_txt, font=placer_font_name, fontsize=placer_font_size, align=placer_align, lineheight=placer_lineHeight)
                            wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=False, morph=(points,m))
                        else:
                            qr_details = box['qr_details'].replace("{", "").replace("}", "")
                            qr_list=list(filter(bool, qr_details.splitlines()))
                            for inv in qr_list:
                                str_val = inv.split("^")
                                str_val_count=len(str_val)
                                mi_txt = mi_txt + extract_plainText(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)                 
                              
                            text_len=len(mi_txt)
                            
                            if placer_lineHeight<3:
                                yOffset=600
                            else:
                                yOffset=100

                            pageW=float(page_rect.width)+int(60)
                            pageH=float(page_rect.height)+int(yOffset)
                            areaPage=int(pageW)*int(pageH)
                            diagonal = round(math.sqrt((pageW**2) + (pageH**2)), 4)
                            font = ImageFont.truetype('arial.ttf', placer_font_size)
                            size = font.getsize(mi_txt)
                            diagonal=float(diagonal) 
                            sizeWInPoints=float(size[0])*float(1.3333)
                            charPerSingleLine=int(math.ceil(diagonal/sizeWInPoints))
                            sizeHInPoints=float(size[1])*float(1.3333)
                            areaPerLine=int(diagonal)* int(math.ceil(sizeHInPoints))
                            totalRepeatText=int(math.ceil(areaPage/areaPerLine))

                            no_of_repeat=int(totalRepeatText) * int(charPerSingleLine)
                            #no_of_repeat= int(no_of_repeat) + int(10) 
                            repeat_txt=mi_txt * int(no_of_repeat)
                            repeat_txt=repeat_txt
                            m = fitz.Matrix(placer_degree_angle)
                            ir = fitz.IRect(page_rect)
                            new_page_rect=ir * m
                            new_page_rect.x1=new_page_rect.x1 #+int(100) 
                            new_page_rect.y1=new_page_rect.y1 #+int(100) 
                            wr = fitz.TextWriter(page.rect)
                            points = fitz.Point(0, 0)  #prect.x0, prect.y0
                            wr.fillTextbox(new_page_rect, repeat_txt, font=placer_font_name, fontsize=placer_font_size, align=placer_align, lineheight=placer_lineHeight)
                            wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=False, morph=(points,m))
                           #  areaPage=int(float(page_rect.width)*int(float(page_rect.height)))
                           #  font = ImageFont.truetype('arial.ttf', placer_font_size)
                           #  size = font.getsize(mi_txt)
                           #  mmWidth= int(math.ceil(float(size[0])*float(0.26458333))) 
                           #  charPerSingleLine=int(math.ceil(364/mmWidth))
                           #  areaPerLine=int(mmWidth)*int(charPerSingleLine)*int(size[1])
                           #  totalRepeatText=int(math.ceil(areaPage/areaPerLine))
                           #  #chrPerLine=int(float(page.rect.width))+int(float(page.rect.height))
                           #  chrPerLine=int(364)
                           #  Totalchrs=str(chrPerLine).split(".")[0]
                           #  no_of_repeat=int(totalRepeatText) * int(charPerSingleLine)
                           #  no_of_repeat= int(no_of_repeat) + int(10) 
                           #  repeat_txt=mi_txt * int(no_of_repeat)
                           # # repeat_txt=mi_txt * int(Totalchrs)
                           #  m = fitz.Matrix(placer_degree_angle)
                           #  ir = fitz.IRect(page_rect)
                           #  new_page_rect=ir * m     
                           #  new_page_rect.x1=new_page_rect.x1+int(150) 
                           #  new_page_rect.y1=new_page_rect.y1+int(200)     
                           #  wr = fitz.TextWriter(page.rect)
                           #  points = fitz.Point(0, 0)
                           #  wr.fillTextbox(new_page_rect, repeat_txt, font=placer_font_name, fontsize=placer_font_size, align=placer_align, lineheight=placer_lineHeight)
                           #  wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=False, morph=(points,m))
                
                if qr_position_check == "On First Page":
                    doc_new = fitz.open()
                    doc_new.insertPDF(doc, from_page=0, to_page=page_count)
                    if verification_setbg=='Yes':
                        for dpage in doc_new:
                            dpage.insertImage(page_rect, verification_bg_file,overlay=False)                    
                    doc_new.save(pdf_folder+"/"+dt_string+".pdf", garbage=4, deflate=True)
                    #doc_new.save(pdf_folder+"/"+dt_string+".pdf", garbage=4, deflate=True, owner_pw="owner", encryption=encrypt_meth, permissions=perm)
        if watermark_check == "On Each Page":
            m = fitz.Matrix(wm_placer_degree_angle)
            ir = fitz.IRect(page_rect)
            new_page_rect=ir * m
            new_page_rect.x1=new_page_rect.x1+int(150) 
            new_page_rect.y1=new_page_rect.y1+int(150)          
            wr = fitz.TextWriter(page.rect)
            points = fitz.Point(0, 0)
            matrix = fitz.Matrix(0,0)
            wr.fillTextbox(new_page_rect, wm_repeat_txt, font=wm_placer_font_name, fontsize=wm_placer_font_size, align=wm_placer_align, lineheight=wm_placer_lineHeight)
            wr.writeText(page, color=wm_placer_font_color, opacity=float(wm_placer_opacity), overlay=False, morph=(points,m))        
        if static_text_check == "On Each Page":
            #font_name = fitz.Font("helv")
            m = fitz.Matrix(0) #st_placer_degree_angle
            points = fitz.Point(static_prect.x0, static_prect.y0)
            wr = fitz.TextWriter(page.rect)
            wr.fillTextbox(static_prect, static_details, font=st_placer_font_name, fontsize=st_placer_font_size, align=st_placer_align)
            #wr.fillTextbox(static_prect, static_details, font=font_name, fontsize=st_placer_font_size, align=st_placer_align)
            wr.writeText(page, color=st_placer_font_color, opacity=float(st_placer_opacity), overlay=True, morph=(points,m))
            #page.addStampAnnot(static_prect, stamp=2)
            if st_placer_font_underline=="underline":
                rl = page.searchFor(static_details)  
                output = str(rl[0]).replace("Rect", "").replace("(", "").replace(")", "")                    
                ucoords = output.split(",")
                urect=fitz.Rect(ucoords[0],ucoords[1],ucoords[2],ucoords[3])
                shape = page.newShape()
                shape.drawLine(urect.bl, urect.br)
                shape.finish(color=st_placer_font_color, stroke_opacity=float(st_placer_opacity))
                shape.commit()            
        if qr_position_check == "On Each Page":            
            doc_new = fitz.open() 
            doc_new.insertPDF(doc, from_page=0, to_page=page_count)
            if verification_setbg=='Yes':
                for dpage in doc_new:
                    dpage.insertImage(page_rect, verification_bg_file,overlay=False)
    
        if print_setbg=='Yes':
            page.insertImage(page_rect, print_bg_file,overlay=False)  

    cnt += 1    
    doc.save(output_file, garbage=4, deflate=True)    
    doc.close()
    
    
connection.commit()    
removePath=rootDir+sys.argv[7]+'/'
#print(removePath)
rewisePath = output_file.replace(removePath,'')
shutil.rmtree(dirName, ignore_errors=True)
print(rewisePath)

