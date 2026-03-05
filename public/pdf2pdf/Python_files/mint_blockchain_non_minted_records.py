#!C:/Users/Administrator/AppData/Local/Programs/Python/Python38/python.exe
from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
import os
import shutil
from shutil import make_archive
from turtle import update
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
import tempfile
import base64


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
  
def split(word): 
    return [char for char in word]


try:
    rootDir = "C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/"
    #update siteid & template_id
    siteid = 257
    template_id = 7
    #update subdomain
    subdomain = 'konkankrishi'
    servername = 'localhost'
    dbName = 'seqr_d_konkankrishi' 
    username = 'developer'
    password = 'developer'

    #update wallet address
    bc_wallet_address = '0x6a87429083C9871a46b411540F2493E36BAE3dd4' 
    #print(bc_wallet_address)
    #exit()
    # mpkv: 0xcbC1410a0fED332BaDEF2c734130927184f1Cb59 | demo: 0xDc4dc0531570Ef61E7Af92781e481407F55221d9
    connection = mysql.connector.connect(host=servername,
                                         database=dbName,
                                         user=username,
                                         password=password)
    if connection.is_connected():
        cursor = connection.cursor(buffered=True)
        cursor.execute("select ep_details, id, extractor_details, template_name, pdf_page, print_bg_file, print_bg_status, verification_bg_file, verification_bg_status, IFNULL(bc_contract_address, '') AS bc_contract_address, bc_document_description, bc_document_type from uploaded_pdfs where id = '%s'" % (template_id))
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
        bc_contract_address=record[9]
        bc_document_description=record[10]
        bc_document_type=record[11]
        #print(extractor_details)
        #cursor.execute("select * from student_table where template_id='%s' AND `status`=1 AND publish=1 AND serial_no ='420403813' limit 1" % (template_id))
        #cursor.execute("select * from student_table where serial_no >='MPKV000008202' AND serial_no <='MPKV000008302' AND template_id='%s' AND updated_at <='2024-01-30 16:40:21' AND `status`=1 AND publish=1 limit 150" % (template_id))
        cursor.execute("select * from student_table where template_id='%s' AND created_at >='2025-05-06' AND status=1 AND publish=1 AND bc_txn_hash IS NULL limit 10" % (template_id))
        records = cursor.fetchall()  
        #print(records)
        row_count = cursor.rowcount
        # print(row_count)
        # exit()
        if row_count > 0: 
            ctr=0

         #   blockchain_response_refresh = requests.get('https://mainnet-apis.herokuapp.com/v1/mainnet/verifyWeb3Session')
          #  print_r(blockchain_response_refresh)
          # exit()
            for row in records:
                serial_no=row[1]
                certificate_filename=row[3]
                barcode_en=row[5] #key
                current_date_time=datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S');
                ctr += 1
                print(str(ctr)+") "+certificate_filename)
                if os.path.exists(rootDir+subdomain+'/backend/pdf_file/'+certificate_filename):
                    doc = fitz.open(rootDir+subdomain+'/backend/pdf_file/'+certificate_filename)    
                    page_count=doc.pageCount
                    cnt = 1
                    for i in doc:
                        page = i
                        if not(page._isWrapped):
                            page._wrapContents()
                        page_data = {cnt:[]}
                        words = page.getTextWords()
                        page_dict = page.getText('dict')    
                        page_rect=page.MediaBox

                        mintData={}
                        mintData["documentType"] = bc_document_type
                        mintData["description"] = bc_document_description
                        mcount=1
                        use_count=0
                        for box_blockchain in extractor_details:
                            if "blockchain_flag" in box_blockchain:
                                if box_blockchain['blockchain_flag'] == 'use':
                                    box_blockchain_show_flag = 1
                                    use_count +=1
                                else:
                                    box_blockchain_show_flag = 0
                            else:
                                box_blockchain_show_flag = 0  

                            if box_blockchain_show_flag == 1:
                                meta_value=''
                                metadata_label = box_blockchain['metadata_label']
                                metadata_value = box_blockchain['metadata_value'].replace("{", "").replace("}", "")
                                meta_list=list(filter(bool, metadata_value.splitlines()))
                                for mdv in meta_list:
                                    str_val = mdv.split("^")
                                    meta_value = meta_value + extract_plainText(extractor_details,str_val[1],str(str_val[0]),str(str_val[2]),page)  
                                mintData['metadata'+str(mcount)]=json.dumps(dict(label = metadata_label, value = meta_value))
                                mcount +=1

                        if use_count > 0 and use_count < 5:
                            for uc in range(use_count+1, 6):
                                mintData['metadata'+str(uc)]=json.dumps(dict({}))   
                        
                        status_code = 201
                        if use_count > 0 and bc_contract_address != '':    
                            pdf_file_path=rootDir+subdomain+"/backend/pdf_file/" + certificate_filename
                            pdf_file_path = pdf_file_path.replace("//", "/")
                            pdf_file_path = pdf_file_path.replace("/", "\\")
                            #print(pdf_file_path)
                            files=[
                                ('document',(certificate_filename,open(pdf_file_path,'rb'),'application/pdf'))
                            ]
                            mintData["description"]=mintData["description"]
                            mintData["walletID"] = bc_wallet_address 
                            mintData["smartContractAddress"] = bc_contract_address 
                            mintData["uniqueHash"] = barcode_en
                            mintData["pdf_file"] = "https://"+subdomain+".seqrdoc.com/"+subdomain+"/backend/pdf_file/" + certificate_filename
                            
                            jsonData=json.dumps(mintData) 
                            print(jsonData)
                            
                            #blockchain_response = requests.post('https://veraciousapis.herokuapp.com/v1/mint', data=mintData, files=files)
                            blockchain_response = requests.post('https://mainnet-apis.herokuapp.com/v1/mainnet/mint', data=mintData, files=files)
                            print(blockchain_response)
                            print(blockchain_response.text)
   
                            if blockchain_response.status_code == 200:
                                status_code = 200
                                status_txt="success"
                            else:
                                status_code = 201
                                status_txt="failed"

                            mint_json = json.loads(blockchain_response.text)          
                        if status_code == 200:                            
                            
                           #print(mint_json['txnHash'], mint_json['gasPrice'], mint_json['tokenID'], barcode_en, current_date_time)
                            sql = "UPDATE student_table SET bc_txn_hash = %s, bc_ipfs_hash = %s , pinata_ipfs_hash = %s WHERE serial_no = %s AND status = %s"
                            val = (mint_json['txnHash'], mint_json['ipfsHash'], mint_json['pinataIpfsHash'], serial_no, '1')
                            cursor.execute(sql, val)
                            
                            sql2 = """INSERT INTO bc_mint_data (`txn_hash`, `gas_fees`, `token_id`, `key`, `created_at`) VALUES (%s, %s, %s, %s, %s)"""
                            val2 = (mint_json['txnHash'], mint_json['gasPrice'], mint_json['tokenID'], barcode_en, current_date_time)
                            cursor.execute(sql2, val2) 

                        jsonData=json.dumps(mintData)  
                        mint_json_txt=json.dumps(mint_json)    
                        sql3 = """INSERT INTO bc_api_tracker (`api_name`, `request_method`, `request_url`, `request_parameters`, `response`, `status`, `created_at`) VALUES (%s, %s, %s, %s, %s, %s, %s)"""
                        val3 = ('mintData', 'POST', 'https://mainnet-apis.herokuapp.com/v1/mainnet/mint', jsonData, mint_json_txt, status_txt, current_date_time)
                        cursor.execute(sql3, val3) 
                        connection.commit()
                       # print('<br>')
                        #exit()   
                else:
                    print('File not found.')
except Error as e:
    print("Error while connecting to MySQL", e)

