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
#print(sys.argv[6])
try:
    directory=sys.argv[8]
    rootDir= directory.replace('excel2pdf', '')
    connection = mysql.connector.connect(host=sys.argv[10],
                                         database=sys.argv[6],
                                         user=sys.argv[11],
                                         password=sys.argv[12])

    connection2 = mysql.connector.connect(host=sys.argv[10],
                                         database='seqr_demo',
                                         user=sys.argv[11],
                                         password=sys.argv[12])

    if connection.is_connected():
        db_Info = connection.get_server_info()
        cursor = connection.cursor()
        if sys.argv[7]=='demo':
            cursor.execute("select ep_details, id, extractor_details, template_name, pdf_page, print_bg_file, print_bg_status, verification_bg_file, verification_bg_status, file_name, bc_contract_address, bc_document_description, bc_document_type from uploaded_pdfs where id = '%s'" % (sys.argv[1]))
        else:
            cursor.execute("select ep_details, id, extractor_details, template_name, pdf_page, print_bg_file, print_bg_status, verification_bg_file, verification_bg_status, file_name from uploaded_pdfs where id = '%s'" % (sys.argv[1]))
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
        if sys.argv[7]=='demo':
            bc_contract_address=record[10]
            bc_document_description=record[11]
            bc_document_type=record[12]    
        cur=connection.cursor()
        print_setbg=''
        verification_setbg=''
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

def text_tl_position(L, T, R, B):
    left_pos=L                  
    top_pos=int(float(T))-4
    right_pos=R
    bottom_pos=int(float(B))               
    prects = fitz.Rect(left_pos,top_pos,right_pos,bottom_pos)   
    prects_coords = left_pos,top_pos,right_pos,bottom_pos
    return prects,prects_coords

def calculate_coordinates(L, T, text_width, text_height):
    left_pos=L                  
    top_pos=int(float(T))-4
    right_pos=text_width
    bottom_pos=int(float(text_height))               
    prects = fitz.Rect(left_pos,top_pos,right_pos,bottom_pos)   
    prects_coords = left_pos,top_pos,right_pos,bottom_pos
    return prects,prects_coords

def getTextlength_custom(text, font_path, font_size):
    # Load the font
    font = ImageFont.truetype(font_path, font_size)

    # Create a blank image (required for ImageDraw)
    dummy_image = Image.new("RGB", (1, 1))
    draw = ImageDraw.Draw(dummy_image)

    # Use textbbox to get the bounding box of the text
    bbox = draw.textbbox((0, 0), text, font=font)

    # Calculate width and height from the bounding box
    width = bbox[2] - bbox[0]
    height = bbox[3] - bbox[1]
    return width, height

    
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
    #dirChars = "F:/projects/flask-app-env/marksheet/grade_card_reader/defence_secure_docs/chars/"+str(p_font_size)
    #dirChars = "C:/Program Files/Python38/projects/demo/chars/"+str(p_font_size)
    dirChars=sys.argv[8]+'Python_files/chars/'+str(int(p_font_size))
    
    name=name.upper()    
    
    single_char=split(name)
    # print(single_char)       
    my_list = list()
    for c in single_char:
        my_list.append(dirChars +"/"+ c +".png")    
    # print(my_list)
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
    im.thumbnail((isize), Image.LANCZOS)
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

def repeat_to_length(string_to_expand, length):
    return (string_to_expand * (int(length/len(string_to_expand))+1))[:length]

def get_pil_text_size(text, font_size, font_name):
    font = ImageFont.truetype(font_name, font_size)
    size = font.getsize(text)
    return size

def hex_to_rgb( hex_color):    
    hex_color = hex_color.lstrip('#')

    if len(hex_color) != 6:
        raise ValueError("Invalid hex color code length (must be 6 digits).")
    
    rgb_tuple = tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))  # Convert to RGB tuple
    return normalize_rgb(rgb_tuple)


def normalize_rgb(rgb):
    """Convert an RGB tuple to normalized values between 0 and 1."""
    return tuple(x / 255 for x in rgb)

def set_background(input_pdf, output, watermark):
    watermark_obj = PdfFileReader(watermark)
    #watermark_obj.decrypt("owner")
    watermark_page = watermark_obj.getPage(0)

    pdf_reader = PdfFileReader(input_pdf)    
    pdf_writer = PdfFileWriter()

    for page in range(pdf_reader.getNumPages()):        
        page = pdf_reader.getPage(page)
        page.mergePage(watermark_page)
        #page.compressContentStreams()
        pdf_writer.addPage(page)

    with open(output, 'wb') as out:
        pdf_writer.write(out)

encrypt_meth = fitz.PDF_ENCRYPT_AES_256  # strongest algorithm
perm = int(
fitz.PDF_PERM_PRINT  # permit printing
)

# dirFont = sys.argv[8]+"Python_files/fonts/"

dirFont = rootDir+sys.argv[7]+"/backend/canvas/fonts/"
#directory="C:/wamp/www/demo/"
#dirFont = "C:/Program Files/Python38/projects/demo/fonts/"
temp_file = template_file.split("/")
doc = fitz.open(rootDir+sys.argv[7]+'/'+template_file)
page_count=doc.pageCount

#Check print limit 
if connection.is_connected():
    cursor.execute("select count(*) AS ts from student_table where site_id = '%s'" % (sys.argv[13]))
    rsStudent = cursor.fetchone()
    studentTableCounts=rsStudent[0]

if connection2.is_connected():
    cursor2 = connection2.cursor()
    siteurl_param=sys.argv[7]+".seqrdoc.com"
    #siteurl_param="demo.seqrdoc.com"
    cursor2.execute("select bc_wallet_address from sites where site_url = '%s'" % (siteurl_param))
    rsSite = cursor2.fetchone()
    bc_wallet_address=rsSite[0]

final_data = {'data':[]}
cnt = 1
pp = pprint.PrettyPrinter(indent=4)
output_file = rootDir+sys.argv[7]+"/processed_pdfs/preview/"+temp_file[2]
preview_folder = rootDir+sys.argv[7]+"/processed_pdfs/preview"
white = (0, 0, 0, 0)
black = (0, 0, 0, 1)
red = (0, 1, 1, 0)
blue = (1, 1, 0, 0)
pink = (0, 1, 0, 0)
aqua = (1, 0, 0, 0)
green = (1, 0, 1, 0)
purple = (1, 1, 0, 0)
yellow = (0, 0, 1, 0)
if not os.path.exists(preview_folder):
	os.makedirs(preview_folder)
#print(path_pdf_moved)



#exit()
for i in doc:
    page = i
    if not(page._isWrapped):
        page._wrapContents()
    page_data = {cnt:[]}
    words = page.getTextWords()
    page_dict = page.getText('dict')    
    page_rect=page.MediaBox
    #print(page_rect)
    #exit()
    dirName = rootDir+sys.argv[7]+"/processed_pdfs/preview/" + str(sys.argv[1])
   # print(dirName)
    blockchain_info={}
    #blockchain_info["documentType"] = bc_document_type
    #blockchain_info["description"] = bc_document_description
    mcount=1
    for box in boxes:
        temp = ''
        otxt = ''
        file_path = ''

        if box['source'] == '' or box['source'] == 'Current DateTime':
            srect = fitz.Rect(0,0,0,0)            
        else:
            placer_coords = box['placer_coords']
            scoords = placer_coords.split(",")
            srect = fitz.Rect(scoords[0],scoords[1],scoords[2],scoords[3])        
        
        placer_coords = box['placer_coords']
        pcoords = placer_coords.split(",")
        prect = fitz.Rect(pcoords[0],pcoords[1],pcoords[2],pcoords[3])
        prect_coords = pcoords[0],pcoords[1],pcoords[2],pcoords[3]
        #print(placer_coords)  
        if box['placer_font_name'] == '':  
            placer_font_name = fitz.Font(fontfile=dirFont+"arial.ttf") 
            # print(placer_font_name)
        else:
            placer_font_name = fitz.Font(fontfile=dirFont+box['placer_font_name'])


        if box['placer_font_name'] == '':  
            placer_font_name = "arial.ttf" 
        else:
            placer_font_name = box['placer_font_name']
        
        
        font_file_path = dirFont
        if placer_font_name == "Crashnumberinggothic_N.ttf":
            placer_font_name = fitz.Font(fontfile=font_file_path+"CrashNumberingGothic_N.otf")
        else:
            placer_font_name = fitz.Font(fontfile=font_file_path+placer_font_name)
            
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

        if placer_opacity is None:
            placer_opacity = 1.0

        if box['line_height'] == '':
            placer_lineHeight = 1
        else:
            placer_lineHeight = box['line_height'] 
        
        if "qr_place" in box:
            if box['qr_place'] == 'show':
                qr_show_flag = 1
            else:
                qr_show_flag = 0
        else:
            qr_show_flag = 1  

        if "blockchain_flag" in box:
            if box['blockchain_flag'] == 'use':
                blockchain_show_flag = 1
            else:
                blockchain_show_flag = 0
        else:
            blockchain_show_flag = 0   

        if "barcode_content" in box:
            if box['barcode_content'] == 'Source Content':
                barcode_content_flag = 1
            else:
                barcode_content_flag = 0
        else:
            barcode_content_flag = 0           

        if "barcode_content_position" in box:
            if box['barcode_content_position'] == 'Text at Bottom':
                barcode_content_position_flag = 1
            else:
                barcode_content_position_flag = 0
        else:
            barcode_content_position_flag = 0        
        
        fontColor = box['font_color']
        search_word = "#"

        if search_word in fontColor:

            placer_font_color = hex_to_rgb(box['font_color'])

            # print(f"The word '{word}' is in the text.")
        else:
            if box['font_color'] == '':
                placer_font_color = black
            else:
                placer_font_color = box['font_color']
                placer_font_color=fitz.utils.getColor(placer_font_color) 
        
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
            if qr_show_flag==1: 
                page.insertImage(prect, qrcode_file, overlay=True)
        elif placer_type == 'QR Dynamic': 
            if not os.path.exists(dirName):
                os.makedirs(dirName) 
            qr_txt=''
            now = datetime.datetime.now()          
            get_text=[]
            if box['source'] == '' or box['source'] == 'Current DateTime':
                #dt_string = now.strftime("%Y-%m-%d %H:%M:%S")+str(cnt)
                dt_string = now.strftime("%Y%m%d%H%M%S")+str(cnt)
                result = hashlib.md5(dt_string.encode()) 
                barcode_en=result.hexdigest()
            else:
                #dt_string = otxt.replace("/", "")+now.strftime("%Y-%m-%d %H:%M:%S")+str(cnt)                
                #dt_string = otxt.replace("/", "")
                #dt_string = otxt
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
            #print(qr_txt)
            
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
            if qr_show_flag==1:
                page.insertImage(prect, qrcode_file, overlay=True)
        elif placer_type == 'Barcode': 
            #get_text=[]
            #get_text.append(page.getTextbox(srect).replace("\n", ""))
            #dt_strings = get_text[0].strip()
            if connection.is_connected():
                today = date.today()
                current_year=get_financial_year(str(today))
                current_year='PN/'+current_year+'/'
                cursor.execute("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '%s'" % (current_year))
                record = cursor.fetchone() 
                next_print=record[0]+1
                next_print_serial=current_year+str(next_print)
            
            if barcode_content_flag==1:
                get_text=[]
                get_text.append(page.getTextbox(srect).replace("\n", ""))
                temp = get_text[0].replace(" ", "").strip()
            else:
                temp = next_print_serial

            if barcode_content_position_flag==1:
                write_text_flag = True
            else:
                write_text_flag = False


            EAN = barcode.get_barcode_class('code128')
            ean = EAN(temp, writer=ImageWriter())
            if isinstance(prect.width, float)==True:
                bcwidth = float(prect.width)
            else:
                bcwidth = int(prect.width)
            
            if isinstance(prect.height, float)==True:
                bcheight = float(prect.height)
            else:
                bcheight = int(prect.height)
            
            rect = fitz.Rect(0, 0.85*bcheight, bcwidth, bcheight)
            #print(w,h) 300 dpi=667 mil

            options = {
                'dpi': 300,
                'write_text': write_text_flag,
                'module_width': bcwidth/667, #0.40
                'module_height': rect.height, #10
                'quiet_zone': 0,
                'text_distance': 1,
                'text_line_distance': 1,
                'font_size': placer_font_size,
				'center_text':True
            }            
            barcode_file = ean.save(dirName, options = options)               
            #page.insertTextbox(prect, str(rect.width))           
            #page.drawRect( prect, color = green, fill = green)           
            im = Image.open(barcode_file)            
            imwidth, imheight = im.size            
            rectwidth = bcwidth*3.7795275591            
            if imwidth>rectwidth:
                page.insertImage(prect, barcode_file, overlay=True)
            else:
                width_in_mm = imwidth*0.2645833333
                newwidth = rectwidth-imwidth
                right_pos = newwidth*0.2645833333
                left_pos=int(float(pcoords[0]))
                top_pos=int(float(pcoords[1]))
                right_pos=int(float(pcoords[2])) - right_pos
                bottom_pos=int(float(pcoords[3]))               
                prects = fitz.Rect(left_pos,top_pos,right_pos,bottom_pos)          
                page.insertImage(prects, barcode_file, overlay=True)           
                         
            #page.insertImage(prect, barcode_file, overlay=True)     
        elif placer_type == 'Print Serial No':      
            if connection.is_connected():
                cursor.execute("SELECT COALESCE(MAX(CONVERT(SUBSTR(serial_no, 4), UNSIGNED)), 0) AS next_num FROM qr_contents")
                record = cursor.fetchone() 
                next_printSN=record[0]+1
                next_print_serial_no=str(next_printSN)
            wr = fitz.TextWriter(page.rect)
            wr.fillTextbox(prect, next_print_serial_no, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
            wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True)        
        elif placer_type == 'Micro Line': 
            mi_txt=''            
            if 'sample_text' in box and box['sample_text'] != '':
                get_text=[]
                get_text.append(box['sample_text'])
                #text_len=len(temp)                
                temp = get_text[0].replace(" ", "").strip()
                search_text = get_text[0].strip()
                #print(type(search_text))
                if placer_font_underline=="underline":
                    rl = doc.loadPage(cnt-1).searchFor(search_text, hit_max=1)
                    #print(rl[0])
                    #print(rl)
                    if page_count==1:
                        output = str(rl[0]).replace("Rect", "").replace("(", "").replace(")", "")
                    else:
                        output = str(rl[len(rl)-1]).replace("Rect", "").replace("(", "").replace(")", "")                    
                    ucoords = output.split(",")
                    urect=fitz.Rect(ucoords[0],ucoords[1],ucoords[2],ucoords[3])
                    #print (urect) 
                    #print (urect.height) 
                    #urect.bl, urect.br
                    #prect_coords = pcoords[0],pcoords[1],pcoords[2],pcoords[3]
                    urect_coords = int(float(ucoords[1]))+int(float(urect.height))-1                    
                    new_urect=fitz.Rect(ucoords[0],urect_coords,ucoords[2],ucoords[3])
                    text_len=len(temp)
                    if isinstance(placer_font_size, float)==True:
                        textwidth = fitz.getTextlength(search_text, fontsize=float(placer_font_size))    
                    else:
                        textwidth = fitz.getTextlength(search_text, fontsize=int(placer_font_size))   
                                
                    #print(textwidth)
                    chrPerLine=int(float(new_urect.width))/textwidth
                    Totalchrs=str(chrPerLine).split(".")[0]
                    #print ("chr = ", chrPerLine)
                    #print (Totalchrs)
                    repeat_txt=temp * int(Totalchrs)
                    print (repeat_txt)
                    if isinstance(placer_font_size, float)==True:
                        repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=float(placer_font_size))    
                    else:
                        repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=int(placer_font_size))   
                    
                    #print(int(float(new_urect.width)),"|",int(textwidth),"|",int(float(repeat_textwidth)))
                    remain_space=int(float(new_urect.width))-int(float(repeat_textwidth))        
                    #print("rs: ",remain_space,"|",text_len)
                    if remain_space>0:                
                        RchrPerLine=int(remain_space)/textwidth
                        remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
                        remain_chrs = temp[0:int(remain_chrs_count)]
                    else:
                        remain_chrs = ''
                     
                    #print(remain_chrs)
                    wr = fitz.TextWriter(page.rect)
                    wr.fillTextbox(new_urect, (temp * int(Totalchrs))+remain_chrs, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                    wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True) 
                    #page.drawRect(new_urect)
                else:
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
                    wr.fillTextbox(prect, (temp * int(Totalchrs))+remain_chrs, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                    wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True)    
        
        elif placer_type == 'Invisible':                        
            invisible_font_color=fitz.utils.getColor("YELLOW")
            inv_txt=''
            get_text=[]
            if 'sample_text' in box and box['sample_text'] != '':
                get_text.append(box['sample_text'])
                otxt=get_text[0].strip()
                # print(otxt)
                #page.insertTextbox(prect, otxt, fontsize=placer_font_size, color=placer_color, align=placer_align, overlay=True)
                wr = fitz.TextWriter(page.rect)
                wr.fillTextbox(prect, otxt,  font=placer_font_name, fontsize=placer_font_size, align=placer_align)
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
                #print(prects)
                #print(prects.width,"| ",prects.height)                         
            else:
                prects=prect         

            get_text=[]

            # col_inx=keys.index(box['source'])
            if 'sample_text' in box and box['sample_text'] != '':            
                otxt_string = box['sample_text']
                chk_otxt_string = box['sample_text']

                get_text.append(otxt_string.replace("\n", ""))

                
                # get_text.append(page.getTextbox(srect).replace("\n", ""))
                #temp = ''.join(c for c in get_text[0].strip().replace("/", "").replace(".", "") if c.isalnum())
                extracted_string = otxt_string
                temp = ''.join(c for c in extracted_string if c.isalnum())
                
                ghost_width = round(float(box['width']) * 3.7795275591)  # Ensure box['width'] is a float
                ghost_height = round(float(box['height']) * 3.7795275591)  # Ensure box['height'] is a float
                # ghost_width = round(box['width'] * 3.7795275591)  # Millimeter to Pixel, 1 mm = 3.7795275591 pixel
                # ghost_height = round(box['height'] * 3.7795275591)  
            
                ghost_words = box['ghost_words']  
                ghost_words = int(ghost_words)
                PrintableChars=temp[ 0 : ghost_words ] #extract first chars
                
                # print(PrintableChars)
                try:
                    placer_degree_angle = int(placer_degree_angle)
                except ValueError:
                    # print(f"Invalid value for placer_degree_angle: {placer_degree_angle}")
                    placer_degree_angle = 0  # Assign a default value or handle the error as needed

                if not os.path.exists(dirName):
                    os.makedirs(dirName)            
                ghostImg=CreateGhostImage(dirName, PrintableChars, placer_font_size, ghost_width, ghost_height)
                page.insertImage(prects, ghostImg,overlay=True, rotate=placer_degree_angle)

        elif placer_type == 'Image':
            #image_path=rootDir+sys.argv[7]+"/upload_images/" + box['image_path']
            image_path=rootDir+sys.argv[7]+"/backend/templates/excel2pdf/"+sys.argv[1]+'/images/' + box['image_path']
            if os.path.exists(image_path):
                page.insertImage(prect, image_path,overlay=True)
        elif placer_type == 'Plain Text':
            
            if 'sample_text' in box and box['sample_text'] != '':

                otxt_string = str(box['sample_text'])
                chk_otxt_string = box['sample_text']
                
                # Check for None or invalid values and convert accordingly
                if otxt_string is None:
                    otxt_string = ""
                



                # Load the font
                # font_path = dirFont+box['placer_font_name']
                if box['placer_font_name'] == '':  
                    font_path = dirFont+"arial.ttf" 
                else:
                    font_path = dirFont+box['placer_font_name']

                nxt_prt_width, nxt_prt_height = getTextlength_custom(otxt_string, font_path, int(placer_font_size))


                if(int(nxt_prt_width) > int(box['width'])):
                    # calculate_coordinates
                    
                    nxt_prt_width_v1 = int(pcoords[0]) + int(nxt_prt_width)
                    nxt_prt_height_v1 = int(pcoords[1])+ int(nxt_prt_height)
                    
                    prects,prects_coords = calculate_coordinates(pcoords[0], pcoords[1], nxt_prt_width_v1, pcoords[3])
                else:
                    prects,prects_coords = text_tl_position(pcoords[0], pcoords[1], pcoords[2], pcoords[3])
                
                

                if placer_degree_angle:
                    # Create an identity matrix and apply rotation
                    m = fitz.Matrix(1, 0, 0, 1, 0, 0).preRotate(placer_degree_angle)
                else:
                    # Fallback in case the angle is not defined (0 degrees rotation)
                    m = fitz.Matrix(1, 0, 0, 1, 0, 0)
                # if placer_degree_angle:
                # m = fitz.Matrix(placer_degree_angle)
                    
                points = fitz.Point(prects.x0, prects.y0)
                
                if otxt_string != 'None':

                    if type(chk_otxt_string) is float:      
                        
                        page.insertTextbox(prects, otxt_string, fontfile=str(placer_font_name), fontsize=placer_font_size, align=placer_align, color=placer_font_color, overlay=True)
                    else:
                        
                        wr = fitz.TextWriter(page.rect)
                        wr.fillTextbox(prects, otxt_string, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                        wr.writeText(page, color=placer_font_color, opacity=float(1.0), overlay=True, morph=(points,m))

        elif placer_type == 'Static Text':     
            otxt_string = box['qr_details']
            m = fitz.Matrix(0) #placer_degree_angle
            points = fitz.Point(prect.x0, prect.y0)
            wr = fitz.TextWriter(page.rect)           
            
            # placer_font_size = float(placer_font_size)

            if isinstance(placer_font_size, float)==True:
                wr.fillTextbox(prect, otxt_string, font=placer_font_name, fontsize=float(placer_font_size), align=placer_align)
            else:
                wr.fillTextbox(prect, otxt_string, font=placer_font_name, fontsize=int(placer_font_size), align=placer_align)

            
            wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=True, morph=(points,m))
            if placer_font_underline=="underline":
                rl = page.searchFor(otxt_string)  
                output = str(rl[0]).replace("Rect", "").replace("(", "").replace(")", "")                    
                ucoords = output.split(",")
                urect=fitz.Rect(ucoords[0],ucoords[1],ucoords[2],ucoords[3])
                shape = page.newShape()
                shape.drawLine(urect.bl, urect.br)
                shape.finish(color=placer_font_color, stroke_opacity=float(placer_opacity))
                shape.commit()            
        elif placer_type == 'Watermark Text':           
            inv_txt=''
            get_text=[]
            if 'sample_text' in box and box['sample_text'] != '':                   
                get_text.append(box['sample_text'])
                otxt_string = get_text[0].strip()
                m = fitz.Matrix(placer_degree_angle)
                ir = fitz.IRect(prect_coords)
                new_rect=ir * m         
                wr = fitz.TextWriter(page.rect)
                points = fitz.Point(prect.x0, prect.y0)                 
                wr.fillTextbox(prect, otxt_string, font=placer_font_name, fontsize=placer_font_size, align=placer_align)
                wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=True, morph=(points,m))                
                 
        elif placer_type == 'Watermark Multi Lines':  
            mi_txt=''            
            if 'sample_text' in box and box['sample_text'] != '':       
                get_text=[]
                get_text.append(box['sample_text'])
                text_len=len(temp)
                temp = get_text[0].strip()+' '
                #chrPerLine=int(float(page_rect.width)+int(float(page_rect.height)))
                chrPerLine=int(390)
                Totalchrs=str(chrPerLine).split(".")[0]
                repeat_txt=temp * int(Totalchrs)       
                m = fitz.Matrix(placer_degree_angle)
                ir = fitz.IRect(page_rect)
                new_page_rect=ir * m 
                #new_page_rect.y0=new_page_rect.y0-int(10)
                #new_page_rect.x0=new_page_rect.x0+int(200)
                new_page_rect.x1=new_page_rect.x1+int(100) 
                new_page_rect.y1=new_page_rect.y1+int(100)     
                wr = fitz.TextWriter(page.rect) #wr = fitz.TextWriter(page.rect)
                points = fitz.Point(0, 0)
                wr.fillTextbox(new_page_rect, repeat_txt, font=placer_font_name, fontsize=placer_font_size, align=placer_align, lineheight=placer_lineHeight)
                wr.writeText(page, color=placer_font_color, opacity=float(placer_opacity), overlay=False, morph=(points,m))

                     
                   # wC=textsize(self, 'ABCD', font=None, *args, **kwargs)
                    #print(wC)
            
        if blockchain_show_flag==1:
            meta_value=''
            metadata_label = box['metadata_label']
            metadata_value = box['metadata_value'].replace("{", "").replace("}", "")
            meta_list=list(filter(bool, metadata_value.splitlines()))
            for mdv in meta_list:
                str_val = mdv.split("^")
                meta_value = meta_value + extract_plainText(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)  
            blockchain_info['metadata'+str(mcount)]=dict(label = metadata_label, value = meta_value)

                #if sys.argv[7]=='demo':
                    #print(mi_txt)
                    #print(totalRepeatText)
                    #print(Totalchrs)
                #     font = ImageFont.truetype('arial.ttf', 12)
                #     size = font.getsize(mi_txt)
                #     print(mi_txt)
                    
        #page.apply_redactions(images=fitz.PDF_REDACT_IMAGE_NONE)
        #page.apply_redactions(images=fitz.PDF_REDACT_IMAGE_REMOVE)
        """ 
        set_background(
            input_pdf=verification_bg_file, 
            output=pdf_folder+"/"+dt_string+".pdf",
            watermark=pdf_folder+"/"+dt_string+".pdf")   
        """       
        mcount +=1
    """
    blockchain_info["walletID"] = bc_wallet_address
    blockchain_info["smartContractAddress"] = bc_contract_address
    blockchain_info["uniqueHash"] = "sc1234_1"
    blockchain_info["pdf_file"] = "https://icat.seqrdoc.com/icat/backend/pdf_file/202109241227161.pdf"
    blockchain_info["template_id"] = 100
    
    if sys.argv[7]=='demo':
        blockchain_response = requests.post('https://veraciousapis.herokuapp.com/v1/mint', blockchain_info)
        print(blockchain_response)
    """
       # print(dirName);

    if print_setbg=='Yes':
        page.insertImage(page_rect, print_bg_file,overlay=False)      
    
    cnt += 1

connection.commit()
print(output_file)

removePath=rootDir+sys.argv[7]+'/'
#print(removePath)
rewisePath = output_file.replace(removePath,'')
doc.save(output_file, garbage=4, deflate=True)
shutil.rmtree(dirName, ignore_errors=True)

# sys.argv[7]+".seqrdoc.com"
print(rewisePath)

