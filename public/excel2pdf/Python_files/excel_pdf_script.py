# import gc
#!C:/wamp64/www/seqr/public/excel_pdf/Python_files/env_excel_pdf/Scripts/python.exe
import io
import os
import shutil
import json
import traceback
import sys
sys.path.append("C:\\inetpub\\vhosts\\seqrdoc.com\\httpdocs\Python\\Python3.8.10\env\\Lib\\site-packages")
import fitz
import mysql.connector
import time
from PIL import Image, ImageOps 
import qrcode
import hashlib 
import pytz
import datetime 
from datetime import date
from PIL import ImageFont
import math
import pandas as pd
import barcode
from barcode.writer import ImageWriter
import base64
from pylibdmtx.pylibdmtx import encode
import asyncio
import certifi
import ssl
# from memory_profiler import profile

context = ssl.create_default_context(cafile=certifi.where())

class PDFGenerator:
    
    async def split(self, word): 
        return [char for char in word]

    async def CreateGhostImage(self, dirName, name, p_font_size, ghost_width, ghost_height, mod_dir_for_font):
        dirChars = os.path.join(mod_dir_for_font, f"backend/chars/{str(p_font_size)}")

        name = name.upper()
        single_char = await self.split(name)    
        my_list = [os.path.join(dirChars, f"{c}.png") for c in single_char]

        total_width, max_height = 0, 0
        image_sizes = []

        for file_path in my_list:
            with Image.open(file_path) as img:
                img = img.resize((int(img.width * 0.5), int(img.height * 0.5)))  # Example resizing to half size
                total_width += img.width
                max_height = max(max_height, img.height)
                image_sizes.append(img.size)
                img.close()
                del img
                # gc.collect()

        new_im = Image.new('RGB', (total_width, max_height))

        x_offset = 0
        for idx, file_path in enumerate(my_list):
            with Image.open(file_path) as im:
                im = im.resize((int(im.width * 0.5), int(im.height * 0.5)))  # Example resizing to half size
                new_im.paste(im, (x_offset, 0))
                x_offset += image_sizes[idx][0]
                im.close()
                del im
                # gc.collect()

        ghostimage1 = os.path.join(dirName, f"{name}.png")
        new_im.save(ghostimage1, quality=100)  # Lower quality to save memory
        del new_im
        # gc.collect()

        path_of_gs_img = f"{name}{p_font_size}_th.png"
        try:
            with Image.open(ghostimage1) as im:
                im.save(os.path.join(dirName, path_of_gs_img), quality=100)  
        except MemoryError as e:
            print(e)
        finally:
            # gc.collect()
            os.remove(ghostimage1)

        return os.path.join(dirName, path_of_gs_img)

    async def add_micro_text_to_rect(self, page, rect, text, font_size, placer_data):
        text_len=len(text)
        if isinstance(font_size, float)==True:
            textwidth = fitz.getTextlength(text, fontsize=float(font_size))    
        else:
            textwidth = fitz.getTextlength(text, fontsize=int(font_size))

        chrPerLine=(int(float(rect.width))/textwidth)
        Totalchrs=str(chrPerLine).split(".")[0]
        repeat_txt=text * int(Totalchrs)

        chrPerLine1=(int(float(rect.height + 2))/textwidth)
        Totalchrs1=str(chrPerLine1).split(".")[0]
        repeat_txt1=text * int(Totalchrs1)

        if isinstance(font_size, float)==True:
            repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=float(font_size))  
            repeat_textwidth1 = fitz.getTextlength(repeat_txt1, fontsize=float(font_size))    
        else:
            repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=int(font_size))
            repeat_textwidth1 = fitz.getTextlength(repeat_txt1, fontsize=int(font_size))

        # ------------------------------------------------------------------------------------
        remain_space=int(float(rect.width))-int(float(repeat_textwidth))-3

        if remain_space>0:                
            RchrPerLine=int(remain_space)/textwidth
            remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
            remain_chrs = text[0:int(remain_chrs_count)]
        else:
            remain_chrs = ''
        
        wl_lst = fitz.getTextlength((text * int(Totalchrs))+remain_chrs, fontsize=font_size)+1
        wl = sum([wl_lst])
        
        if wl>rect.width:      
            remain_space=int(float(rect.width))-int(float(repeat_textwidth))-10     
        else:       
            remain_space=int(float(rect.width))-int(float(repeat_textwidth))-3
        
        if remain_space>0:                
            RchrPerLine=int(remain_space)/textwidth
            remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
            remain_chrs = text[0:int(remain_chrs_count)]
        else:
            remain_chrs = ''

        # placer_font_color = placer_data[14]
        
        # page.drawRect((rect.x0, rect.y0, rect.x1, rect.y0 + font_size), color=(0, 0, 0), width=0.6)
        # top border
        page.insertTextbox(fitz.Rect(rect.x0, rect.y0, rect.x1, rect.y0 + font_size), (text * int(Totalchrs))+remain_chrs,
                            fontsize=font_size, align=fitz.TEXT_ALIGN_CENTER, border_width=0)
        
        # page.drawRect((rect.x0, rect.y1 - font_size, rect.x1, rect.y1), color=(0, 0, 0), width=0.6)
        # bottom border
        page.insertTextbox(fitz.Rect(rect.x0, rect.y1 - font_size, rect.x1, rect.y1), (text * int(Totalchrs))+remain_chrs,
                            fontsize=font_size, align=fitz.TEXT_ALIGN_CENTER, border_width=0)
        # ------------------------------------------------------------------------------------

        remain_space1=int(float(rect.height + 2))-int(float(repeat_textwidth1))-3   

        if remain_space1>0:                
            RchrPerLine=int(remain_space1)/textwidth
            remain_chrs_count1=int(text_len) * int(remain_space1)/int(textwidth)
            remain_chrs1 = text[0:int(remain_chrs_count1)]
        else:
            remain_chrs1 = ''  

        wl_lst1 = fitz.getTextlength((text * int(Totalchrs1))+remain_chrs1, fontsize=font_size)+1
        wl1 = sum([wl_lst1])

        if wl1>rect.height + 2:      
            remain_space1=int(float(rect.height + 2))-int(float(repeat_textwidth1))-10     
        else:       
            remain_space1=int(float(rect.height + 2))-int(float(repeat_textwidth1))-3
        
        if remain_space1>0:                
            RchrPerLine=int(remain_space1)/textwidth
            remain_chrs_count1=int(text_len) * int(remain_space1)/int(textwidth)
            remain_chrs1 = text[0:int(remain_chrs_count1)]
        else:
            remain_chrs1 = ''

        # page.drawRect((rect.x0, rect.y0, rect.x0 + font_size, rect.y1+1), color=(0, 0, 0), width=0.6)
        # left border
        page.insertTextbox(fitz.Rect(rect.x0, rect.y0, rect.x0 + font_size, rect.y1+1), (text * int(Totalchrs1))+remain_chrs1,
                            fontsize=font_size, align=fitz.TEXT_ALIGN_CENTER, rotate=270, border_width=0)
        
        # page.drawRect((rect.x1, rect.y0, rect.x1 + font_size, rect.y1+1), color=(0, 0, 0), width=0.6)
        # right border
        page.insertTextbox(fitz.Rect(rect.x1 - font_size, rect.y0, rect.x1, rect.y1), (text * int(Totalchrs1))+remain_chrs1,
                            fontsize=font_size, align=fitz.TEXT_ALIGN_CENTER, rotate=270, border_width=0)

    async def get_financial_year(self, datestring):
        date = datetime.datetime.strptime(datestring, "%Y-%m-%d").date()
        year_of_date=date.year
        financial_year_start_date = datetime.datetime.strptime(str(year_of_date)+"-04-01","%Y-%m-%d").date()

        if date<financial_year_start_date:
            return str(financial_year_start_date.year-1)[2:]+'-'+ str(financial_year_start_date.year)[2:]
        else:
            return str(financial_year_start_date.year)[2:]+'-'+ str(financial_year_start_date.year+1)[2:]    

    async def mm_to_pixels(self, mm, dpi):
        return mm * dpi / 25.4

    async def pixels_to_mm(self, pixels, dpi):
        return pixels / dpi * 25.4

    async def placer_type_static_image(self, placer_data, arg_instance_name, arg_template_id, rootDir, page, cnt,individual_preview = True):
        try:
            #------------------------------------ optimization -----------------------------------------
            
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = float(placer_data[9])-11
            if float(placer_data[10]) == 0:
                y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)-7
            else:
                y2_px = float(placer_data[10])-7

            prect = (x1, y1, x1+x2_px, y1+y2_px)

            img_var = True
            lt_path = placer_data[17]
            
            image_file_folder = rootDir+arg_instance_name+"/backend/templates/"+str(arg_template_id)
            if not os.path.exists(image_file_folder):
                os.makedirs(image_file_folder)

            img_paath = image_file_folder + f'/{lt_path}' 
            # print(img_paath)
            # if not os.path.exists(img_paath):
            #     error_msg = "Error 404 ", f"<b>Static Image {lt_path} Not Found </b> : <br><p style='color:red;'></p>"
            #     print(error_msg)
            #     # exit()
            #     return{
            #             "status" : 400,
            #             "Message" : "Error 404"f"<b>{error_msg}</b>"
            #         }

            if((individual_preview == True) and (placer_data[31] == '1')):
                # print("skip")
                pass
            else:
                if (str(placer_data[31]) == '1') and (img_var == True):
                    imgs = Image.open(img_paath).convert("L") 
                    img1 = ImageOps.colorize(imgs, black ="yellow", white ="white") 
                    file_path2 = image_file_folder + "/" +"invisible_img_st"+ str(cnt)+".png"
                    img1.save(file_path2, 'png')
                    page.insertImage(prect, file_path2, overlay=True)
                    # print(file_path2)
                    os.remove(file_path2)

                elif str(placer_data[30]) == '0' and (str(placer_data[31]) != '1'):
                    img1 = Image.open(img_paath).convert("L") 
                    file_path2 = image_file_folder + "/" +"greyscale_img"+ str(cnt)+".png"
                    img1.save(file_path2, 'png')
                    page.insertImage(prect, file_path2, overlay=True)
                    # print(file_path2)
                    os.remove(file_path2)
                else:
                    page.insertImage(prect, img_paath, overlay=True)
        
        except Exception as e:
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Static Image: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Image Name :- {placer_data[17]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            error_msg = f"Error while placing {placer_data[4]}"f"Image Name :- {placer_data[17]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>{str(e).replace('omkar_python','project_folder')}</p>"
            
            return{
                        "status" : 400,
                        "Message" : "Error 404"f"<b>{error_msg}</b>"
                    }
                                                   
    async def placer_type_invisible(self, placer_data, mod_dir_for_font, page, row):
        try:
            invisible_font_color=fitz.utils.getColor("YELLOW")   
            text = str(row[placer_data[3]])

            # ----------------------------- font name -----------------------------
            global connection, cursor
            if connection is None or not connection.is_connected():
                # print('--------------------------->1',arg_servername_en, arg_dbName_en, arg_db_username_en, arg_password_en)
                # connection = mysql.connector.connect(host=arg_servername_en, database=arg_dbName_en, user=arg_db_username_en, password=arg_password_en, connection_timeout= 120)
                # cursor = connection.cursor(buffered=True)
                self.connection_retries()

            cursor.execute("select * from font_master where id = '%s' and status = '%s' and publish = '%s'" % (placer_data[12], 1, 1))
            font_record = cursor.fetchone() 

            if font_record == None:
                font_record = (1, 'Arial', 'Arial_N.TTF', 'Arial_B.TTF', None, None, 'Arial')

            # print(placer_data[11])
            # exit()
            if placer_data[11] == 'B':
                font_name=font_record[3]
            elif placer_data[11] == 'I':
                font_name=font_record[4]
            elif placer_data[11] == 'BI':
                font_name=font_record[5]
            else:
                font_name=font_record[2]        


            font_file_path = mod_dir_for_font+"backend/fonts/"
            placer_font_name = fitz.Font(fontfile=font_file_path+font_name)
            font_path = font_file_path + font_name

            text_dimensions = await self.getTextlength_custom(text, font_path, int(placer_data[13]))
            nota, textheight = text_dimensions
            # ----------------------------- font name -----------------------------

            #------------------------------------ optimization -----------------------------------------
            if isinstance(placer_data[13], float)==True:
                # textwidth = fitz.getTextlength(text, fontsize=float(placer_data[13])) 
                textwidth = await self.getTextlength_custom(text, font_path, float(placer_data[13]))  
            else:
                # textwidth = fitz.getTextlength(text, fontsize=int(placer_data[13]))
                textwidth = await self.getTextlength_custom(text, font_path, int(placer_data[13])) 

            textwidth=textwidth[0]
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            # x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
            if (await self.mm_to_pixels(float(placer_data[9]), dpi=72)) > textwidth:
                x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
            else:
                x2_px = textwidth + 10

            if float(placer_data[10]) == 0 or float(placer_data[10]) == -9:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13]) + 3
            else:
                # y2_px = await self.mm_to_pixels(float(placer_data[10]), dpi=72)
                y2_px = textheight

            prect = (x1, y1, x1+x2_px, y1+y2_px)

            align = 0
            if placer_data[6] == 'C':
                align = 1
            elif placer_data[6] == 'R':
                align = 2 

            #------------------------------------ optimization -----------------------------------------     
                    
            wr = fitz.TextWriter(page.rect)
            # wr.fillTextbox(prect, str(df[placer_data[3]].values[0]), font=placer_font_name, fontsize=int(placer_data[13])+3, align=align)
            wr.fillTextbox(prect, str(row[placer_data[3]]), font=placer_font_name, fontsize=int(placer_data[13]), align=align)
            if placer_data[33] != 'null':
                # wr.writeText(page, color=invisible_font_color, opacity=float(0.5), overlay=True)
                wr.writeText(page, color=invisible_font_color, opacity=float(placer_data[33]), overlay=True)
            else:
                wr.writeText(page, color=invisible_font_color, overlay=True)

        except Exception as e:
            text = str(row[placer_data[3]])
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Invisible Text: {'e'}")
            print(
            f"Error while placing {placer_data[4]} Text",
            f"Text :- {text}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            error_msg = (
            f"Error while placing {placer_data[4]} Text",
            f"Text :- {text}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            return{
                "status" : 400,
                "Message" : "Error 404"f"<b>{error_msg}</b>"
            }
            
    async def placer_type_qr_code(self, placer_data, row, is_block_chain, bc_contract_address, arg_instance_name, dirName, page):

        try:
            #------------------------------------ optimization -----------------------------------------
            
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
            if float(placer_data[10]) == 0 or float(placer_data[10]) == -9:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13]) + 3
            else:
                y2_px = await self.mm_to_pixels(float(placer_data[10]), dpi=72)

            place_rect = (x1, y1, x1+x2_px, y1+y2_px)
            
            #------------------------------------ optimization -----------------------------------------

            # ------------- main logic ------------------------
            if placer_data[36] != "{{Dummy Text}}":
                combo_qr_data = placer_data[36].replace("{{","").replace("}}","")
                if "\r\n" in combo_qr_data:
                    combo_qr_data_list = combo_qr_data.split("\r\n")
                else:
                    combo_qr_data_list = combo_qr_data.split("\\r\\n")
                new_list=[]
                for data in combo_qr_data_list:
                    if data == "QR Code":
                        new_list.append(row[placer_data[3]])
                    elif data == "STUDET NAME":
                        new_list.append(row["STUDENT NAME"])
                    else:
                        new_list.append(row[data])

                # qwer="\n".join(new_list)
                qwer = "\n".join(str(item) for item in new_list)

            now = datetime.datetime.now()           
            # unique_sr_no = str(df[placer_data[3]].values[0])
            unique_sr_no = str(row[placer_data[3]])
            dt_string = now.strftime("%Y%m%d%H%M%S")
            combine_str = unique_sr_no + str(dt_string)
            result = hashlib.md5(combine_str.encode()) 
            barcode_en=result.hexdigest()  
            if placer_data[36] != "{{Dummy Text}}" and placer_data[37] == "excel" and is_block_chain !=1:
                qr_txt=qwer + "\n\n" + barcode_en   
            elif placer_data[37] != "excel":
                qr_txt=row[placer_data[16]]
            elif is_block_chain==1 and bc_contract_address != "":
                qr_txt=qwer 
                arr = bytes(barcode_en, 'latin-1')    
                encryptedData=base64.b64encode(arr)
                qr_txt=qr_txt + "\n\n" +"https://"+arg_instance_name+".seqrdoc.com/bverify/" + encryptedData.decode() + "\n" + barcode_en
                # print('------------qr_txt-------3-----', qr_txt)
            else:       
                qr_txt=barcode_en
            
            qr = qrcode.QRCode(version=1, error_correction=qrcode.constants.ERROR_CORRECT_L, box_size=4, border=4)
            qr.add_data(qr_txt)
            # print('------------qr_txt---------------final---', qr_txt)
            qr.make(fit=True)
            img = qr.make_image()  
            # dirName=rootDir+arg_instance_name+"/documents//"
            img.save(dirName+"/"+"qr_"+str(barcode_en)+".png")
            qrcode_file=dirName+"/"+"qr_"+str(barcode_en)+".png"  
            # ------------- main logic ------------------------
            
            page.insertImage(place_rect, qrcode_file, overlay=True)

            return barcode_en
        except Exception as e:
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing QR Code: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            error_msg = (f"Error while placing {placer_data[4]}", 
                                            f"Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
        finally:
            os.remove(qrcode_file)

    async def placer_type_micro_text_border(self, placer_data, row, page):
        try:
            #------------------------------------ optimization -----------------------------------------
            
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = float(placer_data[9])
            if float(placer_data[10]) == 0:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13]) + 3
            else:
                y2_px = float(placer_data[10])

            prects = (x1, y1, (float(placer_data[7])+x2_px), y1+y2_px)

            
            #------------------------------------ optimization -----------------------------------------
        
            # prects = await self.func_for_rect_align_font(cursor, mod_dir_for_font, x_pt=placer_data[7], y_pt=placer_data[8], x_width=placer_data[9], y_height=placer_data[10], text_justification=placer_data[6], font_id=placer_data[12], font_style=placer_data[11], flag_ht_wt=1, flag_neglect_font_align=1, arg_servername=arg_servername, arg_dbName=arg_dbName, arg_db_username=arg_db_username, arg_password=arg_password)

            rect = fitz.Rect(prects)
            # get_text=df[placer_data[3]]
            get_text=row[placer_data[3]]
            await self.add_micro_text_to_rect(page, rect, get_text, 1, placer_data)
        except Exception as e:
            
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Micro Text Border: {'e'}\n{type(e).__name__}\n{e.args}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error:- {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error:- {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
              
    async def placer_type_security_line(self, placer_data, mod_dir_for_font, row, page):
        try:
            placer_font_color = fitz.utils.getColor('black')  

            #------------------------------------ optimization -----------------------------------------

            align = 0
            if placer_data[6] == 'C':
                align = 1
            elif placer_data[6] == 'R':
                align = 2 

            # ----------------------------- font name -----------------------------
            global connection, cursor
            if connection is None or not connection.is_connected():
                self.connection_retries()

            cursor.execute("select * from font_master where id = '%s' and status = '%s' and publish = '%s'" % (placer_data[12], 1, 1))
            font_record = cursor.fetchone() 

            if font_record == None:
                font_record = (1, 'Arial', 'Arial_N.TTF', 'Arial_B.TTF', None, None, 'Arial')

            if placer_data[11] == 'B':
                font_name=font_record[3]
            elif placer_data[11] == 'I':
                font_name=font_record[4]
            elif placer_data[11] == 'BI':
                font_name=font_record[5]
            else:
                font_name=font_record[2]        


            font_file_path = mod_dir_for_font+"backend/fonts/"
            placer_font_name = fitz.Font(fontfile=font_file_path+font_name)
            # ----------------------------- font name -----------------------------

            #------------------------------------ optimization -----------------------------------------


            
            # ------------- main logic ------------------------
            # mi_txt=(row[placer_data[3]]).upper()+" "
            mi_txt = (row[placer_data[3]]).upper() if str(placer_data[15])=='1' else (row[placer_data[3]])
            
            text_len=len(mi_txt)
            placer_font_size=int(placer_data[13]) 

            
            if isinstance(placer_font_size, float)==True:
                textwidth = fitz.getTextlength(mi_txt, fontsize=float(placer_font_size))    
            else:
                textwidth = fitz.getTextlength(mi_txt, fontsize=int(placer_font_size))

            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)-5
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            # x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
            if (await self.mm_to_pixels(float(placer_data[9]), dpi=72)) > textwidth:
                x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)+7
            else:
                x2_px = textwidth + 10
            if float(placer_data[10]) == 0 or float(placer_data[10]) == -9:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13])
            else:
                y2_px = await self.mm_to_pixels(float(placer_data[10]), dpi=72)

            prects = (x1, y1, x1+x2_px, y1+y2_px)
            # page.drawRect((x1, y1, x1+x2_px, y1+y2_px), color=(1, 0, 0), width=0.6)
            prect = fitz.Rect(prects)

            chrPerLine=(round(float(prect.width))/textwidth)
            Totalchrs=str(chrPerLine).split(".")[0]
            repeat_txt=mi_txt * int(Totalchrs)

            if isinstance(placer_font_size, float)==True:
                repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=float(placer_font_size))    
            else:
                repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=int(placer_font_size)) 

            remain_space=int(float(prect.width))-int(float(repeat_textwidth))+11

            if remain_space>0:                
                RchrPerLine=int(remain_space)/textwidth
                remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
                remain_chrs = mi_txt[0:int(remain_chrs_count)]
            else:
                remain_chrs = ''  

            wl_lst = fitz.getTextlength((mi_txt * int(Totalchrs))+remain_chrs, fontsize=placer_font_size)+1
            wl = sum([wl_lst])

            if wl>prect.width:      
                remain_space=int(float(prect.width))-int(float(repeat_textwidth))-10     
            else:       
                remain_space=int(float(prect.width))-int(float(repeat_textwidth))-3
            
            if remain_space>0:                
                RchrPerLine=int(remain_space)/textwidth
                remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
                remain_chrs = mi_txt[0:int(remain_chrs_count)]
            else:
                remain_chrs = ''

                
            wr = fitz.TextWriter(page.rect)
            wr.fillTextbox(prect, (mi_txt * int(Totalchrs))+remain_chrs, font=placer_font_name, fontsize=int(placer_data[13]), align=align)
            # ------------- main logic ------------------------
            if placer_data[33] != 'null':
                wr.writeText(page, color=placer_font_color, opacity=float(placer_data[33]),overlay=True)  
            else:
                wr.writeText(page, color=placer_font_color, overlay=True)  
        except Exception as e:
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Security line: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
    async def placer_type_uv_repeat_line(self, placer_data, mod_dir_for_font, row, page):
        try:
            #------------------------------------ optimization -----------------------------------------

            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
            if float(placer_data[10]) == 0 or float(placer_data[10]) == -9:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13]) + 3
            else:
                y2_px = await self.mm_to_pixels(float(placer_data[10]), dpi=72)

            prects = (x1, y1, x1+x2_px, y1+y2_px)

            align = 0
            if placer_data[6] == 'C':
                align = 1
            elif placer_data[6] == 'R':
                align = 2 

            # ----------------------------- font name -----------------------------
            global connection, cursor
            if connection is None or not connection.is_connected():
                self.connection_retries()

            cursor.execute("select * from font_master where id = '%s' and status = '%s' and publish = '%s'" % (placer_data[12], 1, 1))
            font_record = cursor.fetchone() 

            if font_record == None:
                font_record = (1, 'Arial', 'Arial_N.TTF', 'Arial_B.TTF', None, None, 'Arial')

            if placer_data[11] == 'B':
                font_name=font_record[3]
            elif placer_data[11] == 'I':
                font_name=font_record[4]
            elif placer_data[11] == 'BI':
                font_name=font_record[5]
            else:
                font_name=font_record[2]        


            font_file_path = mod_dir_for_font+"backend/fonts/"
            placer_font_name = fitz.Font(fontfile=font_file_path+font_name)
            # ----------------------------- font name -----------------------------

            #------------------------------------ optimization -----------------------------------------

            prect = fitz.Rect(prects)
            placer_font_color = fitz.utils.getColor('yellow')   
            # ------------------ main logic ----------------------------

            mi_txt=row[placer_data[3]]+" "
            text_len=len(mi_txt)

            placer_font_size=int(placer_data[13])+3

            if isinstance(placer_font_size, float)==True:
                textwidth = fitz.getTextlength(mi_txt, fontsize=float(placer_font_size))    
            else:
                textwidth = fitz.getTextlength(mi_txt, fontsize=int(placer_font_size))

            chrPerLine=(int(float(prect.width))/textwidth)
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

            wl_lst = fitz.getTextlength((mi_txt * int(Totalchrs))+remain_chrs, fontsize=placer_font_size)+1
            wl = sum([wl_lst])

            if wl>prect.width:      
                remain_space=int(float(prect.width))-int(float(repeat_textwidth))-10     
            else:       
                remain_space=int(float(prect.width))-int(float(repeat_textwidth))-3
            
            if remain_space>0:                
                RchrPerLine=int(remain_space)/textwidth
                remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
                remain_chrs = mi_txt[0:int(remain_chrs_count)]
            else:
                remain_chrs = ''

            wr = fitz.TextWriter(page.rect)
            wr.fillTextbox(prect, (mi_txt * int(Totalchrs))+remain_chrs, font=placer_font_name, fontsize=int(placer_data[13])+3, align=align)
            #---------------- main logic --------------------------------
            if placer_data[33] != 'null':
                # wr.writeText(page,  opacity=float(0.5), color=placer_font_color, overlay=True)
                wr.writeText(page,  opacity=float(placer_data[33]), color=placer_font_color, overlay=True)
            else:
                wr.writeText(page,  color=placer_font_color, overlay=True) 

        except Exception as e:
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing UV Repeat line: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg = (f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
             
    async def placer_type_uv_repeat_full_page(self, placer_data, mod_dir_for_font, row, page_rect, page):
        try:
            #------------------------------------ optimization -----------------------------------------

            align = 0
            if placer_data[6] == 'C':
                align = 1
            elif placer_data[6] == 'R':
                align = 2 

            # ----------------------------- font name -----------------------------
            global connection, cursor
            if connection is None or not connection.is_connected():
                self.connection_retries()

            cursor.execute("select * from font_master where id = '%s' and status = '%s' and publish = '%s'" % (placer_data[12], 1, 1))
            font_record = cursor.fetchone() 

            if font_record == None:
                font_record = (1, 'Arial', 'Arial_N.TTF', 'Arial_B.TTF', None, None, 'Arial')

            if placer_data[11] == 'B':
                font_name=font_record[3]
            elif placer_data[11] == 'I':
                font_name=font_record[4]
            elif placer_data[11] == 'BI':
                font_name=font_record[5]
            else:
                font_name=font_record[2]        


            font_file_path = mod_dir_for_font+"backend/fonts/"
            placer_font_name = fitz.Font(fontfile=font_file_path+font_name)
            # ----------------------------- font name -----------------------------

            #------------------------------------ optimization -----------------------------------------


            placer_font_color = fitz.utils.getColor('yellow')       
            
            # get_text=df[placer_data[3]].values[0]
            get_text=row[placer_data[3]]
            textwidth = fitz.getTextlength(get_text, fontsize=float(placer_data[13])+1)   
            
            chrPerLine=(int(float(page_rect.width))/textwidth)
            Totalchrs=str(chrPerLine).split(".")[0]
            repeat_txt=get_text * int(Totalchrs)

            
            repeat_textwidth = fitz.getTextlength(repeat_txt, fontsize=float(placer_data[13])+1)    
            
            remain_space=int(float(page_rect.width))-int(float(repeat_textwidth))-3 
            if remain_space>0:                
                RchrPerLine=int(remain_space)/textwidth
                remain_chrs_count=int(len(get_text)) * int(remain_space)/int(textwidth)
                remain_chrs = get_text[0:int(remain_chrs_count)]
            else:
                remain_chrs = ''                    
                
            wl_lst = fitz.getTextlength((get_text * int(Totalchrs))+remain_chrs, fontsize=float(placer_data[13])+1)+1
            wl = sum([wl_lst])

            yOffset = 600 if int(placer_data[20])<3 else 100
                
            # it calculate all the page height, width, space between each line, what font to use, size and all. Place the watermark lines
            pageW=float(page_rect.width)+int(60)
            pageH=float(page_rect.height)+int(yOffset)
            areaPage=int(pageW)*int(pageH)
            diagonal = round(math.sqrt((pageW**2) + (pageH**2)), 4)
            font = ImageFont.truetype('arial.ttf', int(placer_data[13])+1)
            # size = font.getsize(get_text)
            size = font.getbbox(get_text)
            diagonal=float(diagonal) 
            sizeWInPoints=float(size[2])*float(1.3333)
            charPerSingleLine=int(math.ceil(diagonal/sizeWInPoints))
            sizeHInPoints=float(size[3])*float(1.3333)
            areaPerLine=int(diagonal)* int(math.ceil(sizeHInPoints))
            totalRepeatText=int(math.ceil(areaPage/areaPerLine))

            no_of_repeat=int(totalRepeatText) * int(charPerSingleLine)
            repeat_txt=get_text * int(no_of_repeat)
            m = fitz.Matrix(int(placer_data[18]))
            ir = fitz.IRect(page_rect)
            new_page_rect=ir * m
            new_page_rect.x1=new_page_rect.x1 
            new_page_rect.y1=new_page_rect.y1 


            wr = fitz.TextWriter(page.rect)
            points = fitz.Point(0, 0)  
            wr.fillTextbox(new_page_rect, repeat_txt, font=placer_font_name, fontsize=int(placer_data[13]), align=align, lineheight= await self.pixels_to_mm(round(float(placer_data[20])), 72))
            # print(placer_data[33])
            # exit()
            if placer_data[33] != 'null':
                # wr.writeText(page, color=placer_font_color, opacity=float(0.5),overlay=True, morph=(points,m))
                wr.writeText(page, color=placer_font_color, opacity=float(placer_data[33]),overlay=True, morph=(points,m))
            else:
                # wr.writeText(page, color=placer_font_color, opacity=float(0.5),overlay=True, morph=(points,m))
                wr.writeText(page, color=placer_font_color, opacity=float(placer_data[33]),overlay=True, morph=(points,m))
        
        except Exception as e:
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing UV Repeat Fullpage: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg = (f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                        "status" : 400,
                        "Message" : "Error 404"f"<b>{error_msg}</b>"
                    }
            
    async def placer_type_1d_barcode(self, placer_data, dirName, page):
        try:
            # # -------------rect-----------------------

            # Based on the current year it creates the barcode_serial no
            global connection, cursor
            if connection is None or not connection.is_connected():
                self.connection_retries()

            if connection.is_connected():
                today = date.today()                                               
                current_year=await self.get_financial_year(str(today))                       
                current_year='PN/'+current_year+'/'                               
                cursor.execute("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '%s'" % (current_year))
                record = cursor.fetchone() 
                next_print=record[0]+1                                             
                next_print_serial=current_year+str(next_print)                     # PN/23-24/331
            
            
            temp = next_print_serial

            EAN = barcode.get_barcode_class('code128')
            ean = EAN(temp, writer=ImageWriter())


            left_pos=await self.mm_to_pixels(float(placer_data[7]), 72)
            top_pos=await self.mm_to_pixels(float(placer_data[8]), 72)
            right_pos=await self.mm_to_pixels(float(placer_data[9]), 72)
            bottom_pos=await self.mm_to_pixels(float(placer_data[10]), 72)

            bcwidth = await self.mm_to_pixels(float(placer_data[9]), 72)
            bcheight = float(placer_data[10])
            # rect = fitz.Rect(0, 0.85*bcheight, bcwidth, 1*bcheight+ bcheight)
            # rect = fitz.Rect(0, 0.85*float(placer_data[10]), float(placer_data[9]), float(placer_data[10]))
            # rect = fitz.Rect(0.1*mm_to_pixels(float(placer_data[9]), 96), 0.85*mm_to_pixels(float(placer_data[10]), 96), mm_to_pixels(float(placer_data[9]), 96), mm_to_pixels(float(placer_data[10]), 96))

            # # Draw a red rectangle border
            # page.drawRect((50,50,bcwidth/667, bcheight), color=(1, 0, 0), width=0.6)
            
            options = {
                'dpi': 300,
                'write_text': 0,
                'module_width': bcwidth/667, 
                'module_height': bcheight,
                'quiet_zone': 0
            }            
            # new_dirName = f"D:\\omkar_python\\seqr_application\\public/demo/documents/{str(placer_data[1])}"  # D:\\omkar_python\\seqr_application\\public/pdf2pdf/secura/documents/3/3.png'
            new_dirName = dirName+"/"+str(placer_data[1])+"_12"
            barcode_file = ean.save(new_dirName, options = options)  

            barcode_image = Image.open(new_dirName+".png")             

            barcode_image = barcode_image.convert('RGBA')

            # Create a new image with a transparent background
            transparent_image = Image.new("RGBA", barcode_image.size, (0, 0, 0, 0))

            # Paste the barcode onto the transparent background
            for y in range(barcode_image.height):
                for x in range(barcode_image.width):
                    r, g, b, a = barcode_image.getpixel((x, y))
                    if (r, g, b) == (0, 0, 0):  # if the pixel is black
                        transparent_image.putpixel((x, y), (0, 0, 0, 255))  # keep it black
                    else:
                        transparent_image.putpixel((x, y), (0, 0, 0, 0))  # make it transparent

            # Save the transparent image to a byte stream
            byte_io = io.BytesIO()
            transparent_image.save(byte_io, 'PNG')
            byte_io.seek(0)

            # Save the barcode image with transparent background
            trans_img_path=dirName+"/"+'barcode.png'
            # trans_img_path = f"D:\\omkar_python\\seqr_application\\public/demo/documents/trans_1d_barcode.png"
            transparent_image.save(trans_img_path)  

            prects = fitz.Rect(left_pos,top_pos,left_pos+right_pos,top_pos+bottom_pos)  
            # page.drawRect(prects, color=(1, 0, 0), width=0.6)
            page.insertImage(prects, trans_img_path, overlay=True)
            os.remove(new_dirName+".png")
            os.remove(trans_img_path)
            del new_dirName
            del trans_img_path

        except Exception as e:
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing 1D Barcode: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg = (f"Error while placing {placer_data[4]}", 
                                            f"Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                        "status" : 400,
                        "Message" : "Error 404"f"<b>{error_msg}</b>"
                    }
                    
    async def placer_type_micro_line(self, placer_data, mod_dir_for_font, row, page):
        try:
            #------------------------------------ optimization -----------------------------------------

            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
            y2_px = int(placer_data[13])
            # if float(placer_data[10]) == 0 or float(placer_data[10]) == -9:
            #     y2_px = int(placer_data[13])
            # else:
            #     y2_px = await self.mm_to_pixels(float(placer_data[10]), dpi=72)

            prects = (x1, y1, x1+x2_px, y1+y2_px)

            align = 0
            if placer_data[6] == 'C':
                align = 1
            elif placer_data[6] == 'R':
                align = 2 

            # ----------------------------- font name -----------------------------
            global connection, cursor
            if connection is None or not connection.is_connected():
                self.connection_retries()

            cursor.execute("select * from font_master where id = '%s' and status = '%s' and publish = '%s'" % (placer_data[12], 1, 1))
            font_record = cursor.fetchone() 
            # print('---font_record------->', font_record)

            if font_record == None:
                font_record = (1, 'Arial', 'Arial_N.TTF', 'Arial_B.TTF', None, None, 'Arial')

            if placer_data[11] == 'B':
                font_name=font_record[3]
            elif placer_data[11] == 'I':
                font_name=font_record[4]
            elif placer_data[11] == 'BI':
                font_name=font_record[5]
            else:
                font_name=font_record[2]        




            font_file_path = mod_dir_for_font+"backend/fonts/"
            placer_font_name = fitz.Font(fontfile=font_file_path+font_name)
            # ----------------------------- font name -----------------------------

            #------------------------------------ optimization -----------------------------------------

            prect = fitz.Rect(prects)
            placer_font_color = fitz.utils.getColor('black') 

            mi_txt=row[placer_data[3]]
            # mi_txt="BE 0123456"+" "
            text_len=len(mi_txt)
            placer_font_size=int(placer_data[13])
            
            if isinstance(placer_font_size, float)==True:
                textwidth = fitz.getTextlength(mi_txt, fontsize=float(placer_font_size))    
            else:
                textwidth = fitz.getTextlength(mi_txt, fontsize=int(placer_font_size))
                        
            chrPerLine=(int(float(prect.width))/textwidth)
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

            wl_lst = fitz.getTextlength((mi_txt * int(Totalchrs))+remain_chrs, fontsize=placer_font_size)+1
            wl = sum([wl_lst])

            if wl>prect.width:      
                remain_space=int(float(prect.width))-int(float(repeat_textwidth))-10     
            else:       
                remain_space=int(float(prect.width))-int(float(repeat_textwidth))-3
            
            if remain_space>0:                
                RchrPerLine=int(remain_space)/textwidth
                remain_chrs_count=int(text_len) * int(remain_space)/int(textwidth)
                remain_chrs = mi_txt[0:int(remain_chrs_count)]
            else:
                remain_chrs = ''

            wr = fitz.TextWriter(page.rect)
            if str(placer_data[27]) == '1':
                wr.fillTextbox(prect, (mi_txt * int(Totalchrs))+remain_chrs, font=placer_font_name, fontsize=int(placer_data[13]), align=align)
            else:
                wr.fillTextbox(prect, mi_txt, font=placer_font_name, fontsize=int(placer_data[13]), align=align)
            wr.writeText(page, color=placer_font_color, overlay=True)

        except Exception as e:
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Micro line: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
    async def placer_type_2d_barcode(self, placer_data, row, dirName, page):
        try:
            #------------------------------------ optimization -----------------------------------------
            
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = float(placer_data[9])
            if float(placer_data[10]) == 0:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13]) + 3
            else:
                y2_px = float(placer_data[10])

            place_rect1 = (x1, y1, x1+x2_px, y1+y2_px)

            #------------------------------------ optimization -----------------------------------------

            
            # data = str(df[placer_data[3]].values[0])
            data = str(row[placer_data[3]])
            # Generate Data Matrix barcode
            encoded = encode(data.encode('utf-8'))
            # Convert the encoded bytes to an image
            image = Image.frombytes('RGB', (encoded.width, encoded.height), encoded.pixels)

            # Convert to RGBA to add an alpha channel for transparency
            image = image.convert('RGBA')

            # Create a new image with a transparent background
            transparent_image = Image.new('RGBA', image.size, (0, 0, 0, 0))

            # Paste the Data Matrix code onto the transparent background
            for y in range(image.height):
                for x in range(image.width):
                    r, g, b, a = image.getpixel((x, y))  # Accommodate for RGBA
                    if (r, g, b) == (0, 0, 0):  # if the pixel is black
                        transparent_image.putpixel((x, y), (0, 0, 0, 255))  # keep it black
                    else:
                        transparent_image.putpixel((x, y), (0, 0, 0, 0))  # make it transparent

            # Save the image to a byte stream
            byte_io = io.BytesIO()
            transparent_image.save(byte_io, 'PNG')
            byte_io.seek(0)

            # img_pathh=r'D:\omkar_python\seqr_application\scratch_0905\datamatrix.png'
            # img_pathh=rootDir+arg_instance_name+"/documents/datamatrix.png"
            img_pathh=dirName+"/datamatrix.png"
            # Save the image for manual verification
            transparent_image.save(img_pathh)
            
            page.insertImage(place_rect1, img_pathh, overlay=True)

        
        except Exception as e:
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing 2D Barcode: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg = (f"Error while placing {placer_data[4]}", 
                                            f"Text :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
        finally:
            os.remove(img_pathh)

    async def placer_type_dynamic_image(self, row, placer_data, arg_instance_name, arg_template_id, template_id, rootDir, cnt, page, individual_preview = True):
        global error_flag
        try:
            file_path2 = None
            dynamic_img = row[placer_data[3]]
            image_file_folder = rootDir + arg_instance_name + "/backend/templates/" + str(template_id)

            # Ensure the image folder exists
            # if not os.path.exists(image_file_folder):
            #     os.makedirs(image_file_folder)

            #------------------------------------ optimization -----------------------------------------
            
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = float(placer_data[9])
            if float(placer_data[10]) == 0:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13]) + 3
            else:
                y2_px = float(placer_data[10])

            prect = (x1, y1, x1+x2_px, y1+y2_px)

            #------------------------------------ optimization -----------------------------------------

            # Check if `dynamic_img` has an extension
            
            has_extension = '.' in os.path.basename(dynamic_img)

            if has_extension:
                # Old code logic (no changes if dynamic_img has an extension)
                # image_url = f'https://{arg_instance_name}.seqrdoc.com/{arg_instance_name}/backend/templates/{arg_template_id}/{dynamic_img.replace(" ", "%20")}'
                img_paath = os.path.join(image_file_folder, dynamic_img)
                # if not os.path.exists(img_paath):
                #     print("Error 404 ", f"<b>Dynamic Image Not Found </b> : <br>")
                #     return{
                #         "status" : 400,
                #         "Message" : "Error 404"f"<b>{error_msg}</b>"
                #         }
                    # try:
                    #     with urllib.request.urlopen(image_url, context=context) as response, open(img_paath, 'wb') as out_file:
                    #         data_1 = response.read()
                    #         out_file.write(data_1)
                    # except Exception as e:
                    #     error_flag = True
                    #     print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Dynamic Image Not Found: {'e'}")
                    #     print("Error 404 ", f"<b>Dynamic Image Not Found </b> : <br><br>{'e'}<br>")
            
            # else:
            #     # Try appending extensions sequentially
            #     possible_extensions = [".png", ".jpg", ".jpeg"]
            #     img_paath = None
            #     found_image = False

            #     for ext in possible_extensions:
                    
            #         dynamic_img_with_ext = dynamic_img + ext
            #         image_url = f'https://{arg_instance_name}.seqrdoc.com/{arg_instance_name}/backend/templates/{arg_template_id}/{dynamic_img_with_ext.replace(" ", "%20")}'
            #         img_paath_candidate = os.path.join(image_file_folder, dynamic_img_with_ext)

            #         if not os.path.exists(img_paath_candidate):
            #             print(f"Failed to fetch image with extension '{ext}': {'e'}")
            #             try:
            #                 # Attempt to download the file
            #                 with urllib.request.urlopen(image_url, context=context) as response, open(img_paath_candidate, 'wb') as out_file:
            #                     out_file.write(response.read())
            #                 img_paath = img_paath_candidate
            #                 found_image = True
            #                 break
            #             except Exception as e:
            #                 # Log failed attempts and continue to the next extension
            #                 print(f"Failed to fetch image with extension '{ext}': {'e'}")
            #                 continue

                # if not found_image:
                #     error_flag = True
                #     error_msg = f"Dynamic Image Not Found for {dynamic_img} with extensions (.png, .jpg, .jpeg)"
                #     print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - {error_msg}")
                #     print("Error 404", f"<b>{error_msg}</b><br>")
                    
                #     return{
                #             "status" : 400,
                #             "Message" : "Error 404"f"<b>{error_msg}</b>"
                #         }

            # Proceed with image processing
            is_uv_image = placer_data[31]
            if ((individual_preview == True) and (is_uv_image == "1")):
                # print("skip")
                pass
            else:
                if is_uv_image == '1':
                    imgs = Image.open(img_paath).convert("L")
                    img1 = ImageOps.colorize(imgs, black="white", white="yellow")
                    file_path2 = os.path.join(image_file_folder, f"invisible_img{cnt}.png")
                    img1.save(file_path2, 'png')
                    page.insertImage(prect, file_path2, overlay=True)
                    os.remove(file_path2)
                elif str(placer_data[30]) == '1':
                    page.insertImage(prect, img_paath, overlay=True)
                else:
                    img1 = Image.open(img_paath).convert("L")
                    file_path2 = os.path.join(image_file_folder, f"greyscale_img{cnt}.png")
                    img1.save(file_path2, 'png')
                    page.insertImage(prect, file_path2, overlay=True)
                    os.remove(file_path2)

        except Exception as e:
            error_flag = True
            traceback.print_exc()  # Prints the full traceback to standard output
            # You can also capture the traceback as a string for logging
            error_message = traceback.format_exc()
            # print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Dynamic Image: {error_message}")
            print(
                f"Error while placing {placer_data[4]}",
                f"Image Name :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python', 'project_folder')}</p><br>")
            error_msg=(
                f"Error while placing {placer_data[4]}",
                f"Image Name :- {row[placer_data[3]]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python', 'project_folder')}</p><br>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
    async def placer_type_normal_or_static_text(self, placer_data, mod_dir_for_font, page, row):
        try:
            # ----------------------------- font name -----------------------------
            global connection, cursor
            if connection is None or not connection.is_connected():
                self.connection_retries()
            
            text = placer_data[16] if placer_data[4]=='Static Text' else str(row[placer_data[3]])
            if placer_data[34]!='1':
                if text!='nan':
                    
                    if connection.is_connected:
                        cursor.execute("select * from font_master where id = '%s' and status = '%s' and publish = '%s'" % (placer_data[12], 1, 1))
                        font_record = cursor.fetchone() 

                        if font_record == None:
                            font_record = (1, 'Arial', 'Arial_N.TTF', 'Arial_B.TTF', None, None, 'Arial')
                        
                        if placer_data[11] == 'B':
                            font_name=font_record[3]
                        elif placer_data[11] == 'I':
                            font_name=font_record[4]
                            # print(font_name)
                        elif placer_data[11] == 'BI':
                            font_name=font_record[5]
                        else:
                            font_name=font_record[2]        

                        # print('---------font_name-------', font_name)
                        font_file_path = mod_dir_for_font+"backend/fonts/"
                        if font_name == "Crashnumberinggothic_N.ttf":
                            placer_font_name = fitz.Font(fontfile=font_file_path+"CrashNumberingGothic_N.otf")
                            font_path = font_file_path + "CrashNumberingGothic_N.otf" 
                        else:
                            placer_font_name = fitz.Font(fontfile=font_file_path+font_name)
                            font_path = font_file_path + font_name

                        # print(font_path)
                        # exit()

                        text_dimensions = await self.getTextlength_custom(text, font_path, int(placer_data[13]))
                        nota, textheight = text_dimensions

                        # ----------------------------- font name -----------------------------
                        

                        #------------------------------------ optimization -----------------------------------------

                        wr = fitz.TextWriter(page.rect)

                        # text = placer_data[16] if placer_data[4]=='Static Text' else str(row[placer_data[3]])
                        # text = placer_data[16] if placer_data[4] == 'Static Text' else (str(row[placer_data[3]]) if pd.notna(row[placer_data[3]]) else '')

                        placer_font_size=int(placer_data[13])

                        if isinstance(placer_font_size, float)==True:
                            # textwidth = fitz.getTextlength(text, fontsize=float(placer_font_size))  
                            textwidth = await self.getTextlength_custom(text, font_path, float(placer_font_size))  
                        else:
                            # textwidth = fitz.getTextlength(text, fontsize=int(placer_font_size))
                            textwidth = await self.getTextlength_custom(text, font_path, int(placer_font_size))

                        textwidth=textwidth[0]
                        #-------------------------------------------------------------------------------------------------
                        x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
                        y1 = await self.mm_to_pixels(float(placer_data[8]), 72)

                        if (await self.mm_to_pixels(float(placer_data[9]), dpi=72)) > textwidth:
                            x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
                        else:
                            x2_px = textwidth + 10

                        if float(placer_data[10]) == 0 or float(placer_data[10]) == -9:
                            # y2_px = await self.mm_to_pixels(8, dpi=72)
                            if placer_data[28]=='1':
                                y2_px = placer_font_size* placer_font_size + 10
                                x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
                            else:
                                y2_px = textheight 
                        else:
                            y2_px = await self.mm_to_pixels(float(placer_data[10]), dpi=72)

                        place_rect = (x1, y1, x1+x2_px, y1+y2_px)
                        # page.drawRect(place_rect, color=(0, 0, 0), width=0.6)

                        align = 0
                        if placer_data[6] == 'C':
                            align = 1
                        elif placer_data[6] == 'R':
                            align = 2 

                        #-------------------------------------------------------------------------------------------------
                        placer_font_color = self.hex_to_rgb(placer_data[14])

                        wr.fillTextbox(place_rect, text, font=placer_font_name, fontsize=int(placer_data[13]), align=align)
                        if (placer_data[33] == 'null') or (placer_data[33] == None):
                            wr.writeText(page, color = placer_font_color, overlay=True)
                        else:
                            wr.writeText(page, color = placer_font_color, opacity=float(placer_data[33]), overlay=True)

                    else:
                        print('-------------else--------pRT------')
                        self.placer_type_normal_or_static_text(placer_data, mod_dir_for_font, page, row)

        except Exception as e:
            print("-------------------------",textwidth)
            text = placer_data[16] if placer_data[4]=='Static Text' else str(row[placer_data[3]])
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Normal or Static Text: {'e'}")
            print(
            f"Error while placing {placer_data[4]} Text",
            f"Text :- {text}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color:red;'>Error : {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=(
            f"Error while placing {placer_data[4]} Text",
            f"Text :- {text}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color:red;'>Error : {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
    def hex_to_rgb(self, hex_color):
        hex_color = hex_color.lstrip('#')
        if len(hex_color) != 6:
            raise ValueError("Invalid hex color code length (must be 6 digits).")
        
        rgb_tuple = tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))  # Convert to RGB tuple
        return self.normalize_rgb(rgb_tuple)
    
    def normalize_rgb(self, rgb):
        """Convert an RGB tuple to normalized values between 0 and 1."""
        return tuple(x / 255 for x in rgb)

    async def placer_type_yellow_patch(self, mod_dir_for_font, row, placer_data, page):
        try:
            # image_path = r"D:\omkar_python\seqr_application\scratch_0905\modified_image1.png"
            image_path = mod_dir_for_font+"backend/canvas/yellowpatchimages/modified_yellow_patch.png"
            # image = Image.open(image_path).convert("RGB")
            # image.save(image_path, quality=100)
            
            font_file_path = mod_dir_for_font+"backend/fonts/"
            placer_font_name = fitz.Font(fontfile=font_file_path+r"/arialB.ttf")
            
            # get_text=df[placer_data[3]].values[0]
            get_text=row[placer_data[3]]
            temp = ''.join(c for c in get_text.strip().replace("/", "").replace(".", "") if c.isalnum())  
            # -------------rect-----------------------
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            ghost_width = 145.1496063  
            ghost_height = 32.125984252                                               
            ghost_words = placer_data[21]                                                                  
            rect = fitz.Rect(x1, y1, x1+ghost_width, y1+ghost_height)
            # -------------rect-----------------------
            PrintableChars=temp[ 0 : int(ghost_words)]   

            wr = fitz.TextWriter(page.rect)
            page.insertImage(rect, filename=image_path)
            # Free memory
            # del image
            # gc.collect()

            rect = fitz.Rect(x1-7, y1+4, x1+ghost_width, y1+ghost_height)
            wr.fillTextbox(rect, PrintableChars.upper(), font=placer_font_name, fontsize=22, align=1)
            placer_font_color=fitz.utils.getColor('yellow') 
            wr.writeText(page, color=placer_font_color, overlay=True, opacity=0.4)  
        
        except Exception as e:
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Yellow Patch: {'e'}")
            print("Error while placing Yellow Patch", f"Error : <p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=("Error while placing Yellow Patch", f"Error : <p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
    async def placer_type_ghost_image(self, placer_data, row, dirName, page, mod_dir_for_font):
        try:
            # -------------rect-----------------------
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            # x2_px = await self.mm_to_pixels(float(placer_data[9]), dpi=72)
            # if float(placer_data[10]) == 0:
            #     y2_px = await self.mm_to_pixels(9.2333333333, dpi=72)
            # else:
            #     y2_px = await self.mm_to_pixels(float(placer_data[10]), dpi=72)
        
            # x2 = (x1 + 164)
            # y2 = (y1 + 28)
            if str(placer_data[13]) == '12':
                x2 = (x1 + 140)
                y2 = (y1 + 23)
            elif str(placer_data[13]) == '11':
                x2 = (x1 + 119)
                y2 = (y1 + 20)
            elif str(placer_data[13]) == '10':
                x2 = (x1 + 94)
                y2 = (y1 + 14)
            elif str(placer_data[13]) == '13':
                x2 = (x1 + 175)
                y2 = (y1 + 29)
            elif str(placer_data[13]) == '14':
                x2 = (x1 + 217)
                y2 = (y1 + 35)
            elif str(placer_data[13]) == '15':
                x2 = (x1 + 242)
                y2 = (y1 + 40)
            elif str(placer_data[13]) == '16':
                x2 = (x1 + 164)
                y2 = (y1 + 28)
            # offsets = {
            #     '10': (94, 14),
            #     '11': (119, 20),
            #     '12': (140, 23),
            #     '13': (175, 29),
            #     '14': (217, 35),
            #     '15': (242, 40),
            #     '16': (164, 28),
            # }

            # # Get the offsets based on the value of placer_data[13]
            # x2_offset, y2_offset = offsets.get(str(placer_data[13]), (0, 0))

            # # Calculate x2 and y2 using the offsets
            # x2 = x1 + x2_offset
            # y2 = y1 + y2_offset
            
        

            prects = (x1, y1, x2, y2)   
            # -------------rect-----------------------

            
            # ------------- main logic ------------------------
            get_text=row[placer_data[3]]
            temp = ''.join(c for c in get_text.strip().replace("/", "").replace(".", "") if c.isalnum())  
            ghost_width = round(x2 * 3.7795275591)  
            ghost_height = round(y2 * 3.7795275591)                                                 
            ghost_words = placer_data[21]                                                                  
            PrintableChars=temp[ 0 : int(ghost_words)]     

            # dirName = r"D:\omkar_python\seqr_application\scratch_0905"
            # dirName = mod_dir_for_font+"static/images/"
            ghostImg=await self.CreateGhostImage(dirName, PrintableChars, placer_data[13], ghost_width, ghost_height, mod_dir_for_font)
            # ------------- main logic ------------------------
            page.insertImage(prects, filename=ghostImg,overlay=True, rotate=int(placer_data[18]))

        except Exception as e:
            text = row[placer_data[3]]
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Ghost Image: {'e'}")
            print(f"Error while placing {placer_data[4]}", 
                                            f"Ghost Text :- {text[ 0 : int(placer_data[21])]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=(f"Error while placing {placer_data[4]}", 
                                            f"Ghost Text :- {text[ 0 : int(placer_data[21])]}<br>Placer Type :- {placer_data[4]}<br>Excel Column Name :- {placer_data[3]}<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
        finally:
            if 'ghostImg' in locals() and os.path.exists(ghostImg):
                os.remove(ghostImg)

    async def placer_type_anti_copy(self, placer_data, mod_dir_for_font, page):
        try:
            y_height = 10 if placer_data[10] == '0' else placer_data[10]
            #------------------------------------ optimization -----------------------------------------
            
            x1 = await self.mm_to_pixels(float(placer_data[7]), 72)
            y1 = await self.mm_to_pixels(float(placer_data[8]), 72)
            x2_px = await self.mm_to_pixels(float(int(placer_data[9])+20), dpi=72)
            if float(y_height) == 0:
                # y2_px = await self.mm_to_pixels(4.2333333333, dpi=72)
                y2_px = int(placer_data[13]) + 3
            else:
                y2_px = await self.mm_to_pixels(float(y_height), dpi=72)

            prects = (x1, y1, x1+x2_px, y1+y2_px)

            #------------------------------------ optimization -----------------------------------------

            prect = fitz.Rect(prects)

            img_paath= mod_dir_for_font+"backend/canvas/ghost_images/Void_1.png"
            page.insertImage(prect, img_paath, overlay=True)
        
        except Exception as e:
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing Anti-Copy: {'e'}")
            print("Error while placing Anti-Copy", f"Error Details : <p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=("Error while placing Anti-Copy", f"Error Details : <p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
    async def placer_type_id_barcode(self, placer_data, cnt, dirName, page, mod_dir_for_font):
        try:
            # -------------rect-----------------------
            left_pos=await self.mm_to_pixels(float(placer_data[7]), 72)
            top_pos=await self.mm_to_pixels(float(placer_data[8]), 72)
            right_pos=await self.mm_to_pixels(float(placer_data[9]), 72)
            bottom_pos=await self.mm_to_pixels(float(placer_data[10]), 72)

            bcwidth = await self.mm_to_pixels(float(placer_data[9]), 72)
            bcheight = float(placer_data[10])
            rect = fitz.Rect(0.1*(await self.mm_to_pixels(float(placer_data[9]), 72)), 0.85*(await self.mm_to_pixels(float(placer_data[10]), 72)), (await self.mm_to_pixels(float(placer_data[9]), 72)), (await self.mm_to_pixels(float(placer_data[10]), 72)))
            # -------------rect-----------------------

            # ------------------ logic for text  ----------------------------------------
            # Based on the current year it creates the barcode_serial no
            global connection, cursor
            if connection is None or not connection.is_connected():
                self.connection_retries()

            if connection.is_connected():
                today = date.today()                                               
                current_year=await self.get_financial_year(str(today))                       
                current_year='PN/'+current_year+'/'                               
                cursor.execute("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '%s'" % (current_year))
                record = cursor.fetchone() 
                # next_print=record[0]+1 
                next_print=record[0]+cnt                                             
                next_print_serial=current_year+str(next_print)                     
            
            
            temp = next_print_serial
            EAN = barcode.get_barcode_class('code128')
            ean = EAN(temp, writer=ImageWriter())

            font_path = mod_dir_for_font+"backend/fonts/" + "ArialB.TTF"

            options = {
                'dpi': 300,
                'write_text': 1,
                'module_width': bcwidth/667, 
                'module_height': rect.height,
                'quiet_zone': 0,
                'text_distance': 1,
                'text_line_distance': 1,
                'font_size': 16,
                'font_path': font_path,
                'center_text':True
            }            
            # new_dirName = rootDir+arg_instance_name+"/documents//"+str(placer_data[1])
            new_dirName = dirName+"/"+str(placer_data[1])
            # new_dirName = f"D:\\omkar_python\\seqr_application\\public/demo/documents/{str(placer_data[1])}"  # D:\\omkar_python\\seqr_application\\public/pdf2pdf/secura/documents/3/3.png'
            barcode_file = ean.save(new_dirName, options = options)  
            # ------------------- logic for text  ---------------------------------------


            # ---------------------------- Transaparent bg ------------------------------
            barcode_image = Image.open(barcode_file)             

            # Convert the image to RGBA (to add transparency)
            barcode_image = barcode_image.convert("RGBA")

            # Create a new image with a transparent background
            transparent_image = Image.new("RGBA", barcode_image.size, (0, 0, 0, 0))

            # Paste the barcode onto the transparent background
            for y in range(barcode_image.height):
                for x in range(barcode_image.width):
                    r, g, b, a = barcode_image.getpixel((x, y))
                    if (r, g, b) == (0, 0, 0):  # if the pixel is black
                        transparent_image.putpixel((x, y), (0, 0, 0, 255))  # keep it black
                    else:
                        transparent_image.putpixel((x, y), (0, 0, 0, 0))  # make it transparent

            # Save the transparent image to a byte stream
            byte_io = io.BytesIO()
            transparent_image.save(byte_io, 'PNG')
            byte_io.seek(0)

            # Save the image for manual verification (optional)
            path=dirName+"/transparent_datamatrix.png"
            transparent_image.save(path)
            # ---------------------------- Transaparent bg ------------------------------
            

            prects = fitz.Rect(left_pos,top_pos,left_pos+right_pos,top_pos+bottom_pos)  
            page.insertImage(prects, stream=byte_io, overlay=True)
            os.remove(new_dirName+".png")
            os.remove(path)

        except Exception as e:
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception while placing ID Barcode: {'e'}")
            print("Error while placing ID Barcode", f"Error Details : <p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            error_msg=("Error while placing ID Barcode", f"Error Details : <p style='color:red;'>{str(e).replace('omkar_python','project_folder')}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
    async def getTextlength_custom(self, text, font_path, fontsize):
        from PIL import ImageFont
        
        font = ImageFont.truetype(font_path, fontsize)
        # size = font.getsize(text)  # This returns a tuple (width, height)
        size = font.getbbox(text)
        # return size
    
        dimensions = (size[2], size[3])
        # print(dimensions)
        return dimensions
    
    async def save_single_page_as_pdf(self,pdf_file_path,print_bg_file,page_width,page_height,page,is_printable=False):
        try:
            # Get the source document from which the page comes.
            src_doc = page.parent

            # Create a new empty PDF document
            new_doc = fitz.open()
            x1 = 0
            y1 = 0
            x2 = await self.mm_to_pixels(page_width, dpi=72)
            y2 = await self.mm_to_pixels(page_height, dpi=72)
        
            page_rect1 = (x1, y1, x2, y2)

            # new_page.show_pdf_page(new_page.rect, page.parent, page.number)
            new_doc.insertPDF(src_doc, from_page=page.number, to_page=page.number)

            if is_printable == False:
                new_page = new_doc[0]

                new_page.insertImage(page_rect1, print_bg_file,overlay=False)
                new_doc.save(
                pdf_file_path,
                garbage=6,  # Aggressive garbage collection (remove unused objects)
                deflate=True,  # Apply compression (deflate streams)
                clean=True
                )
            else:
                new_doc.save(pdf_file_path, garbage=6, deflate=True, clean=True, incremental=False)


            # Save the new document with the page at the specified file path
            new_doc.close()

            del src_doc

            # Explicitly call garbage collector to free memory
            # del new_doc, print_bg_file

            # print(f"PDF saved successfully at {pdf_file_path}")

        except Exception as e:
            print(f"An error occurred: {str(e)}")
            return {
                "status": 500,
                "Message": f"Failed to save PDF: {str(e)}"
            }

    # def merge_pdfs(self, directory, output_file,excel_file,column_name="Unique Sr no",batch_size=1000):
    #     # pdf_files = sorted(
    #     #     [os.path.join(directory, filename) for filename in os.listdir(directory) if filename.endswith(".pdf")]
    #     # )
    #      # Read Excel file
    #     df = pd.read_excel(excel_file, dtype={column_name: str})  # Ensure filenames are treated as strings
    #     ordered_filenames = df[column_name].dropna().tolist()  # Remove NaN values and get list
    #     # print('--')
    #     # Convert Excel file names to actual PDF file names
    #     pdf_files = [
    #         os.path.join(directory, f.replace("/", "_") + ".pdf")  
    #         for f in ordered_filenames
    #         if os.path.exists(os.path.join(directory, f.strip().replace("/", "_") + ".pdf"))
    #     ]

    #     # print(pdf_files)
        
    #     temp_files = []
    #     for i in range(0, len(pdf_files), batch_size):
    #         pdf_writer = fitz.open()
    #         batch = pdf_files[i:i + batch_size]

    #         for pdf_path in batch:
    #             with fitz.open(pdf_path) as pdf_document:
    #                 pdf_writer.insertPDF(pdf_document)

    #         temp_file = f"temp_{i}.pdf"
    #         pdf_writer.save(temp_file, garbage=6, deflate=True, clean=True, incremental=False)
    #         pdf_writer.close()
    #         temp_files.append(temp_file)

    #     # Merge all temp files
    #     final_writer = fitz.open()
    #     for temp_file in temp_files:
    #         with fitz.open(temp_file) as temp_pdf:
    #             final_writer.insertPDF(temp_pdf)
    #         os.remove(temp_file)  # Optionally remove temp files

    #     final_writer.save(output_file, garbage=6, deflate=True, clean=True, incremental=False)
    #     final_writer.close()
    def merge_pdfs(self, directory, output_file,excel_file,column_name="Unique Sr no",batch_size=1000):
        df = pd.read_excel(excel_file, dtype={column_name: str})  # Ensure filenames are treated as strings
        ordered_filenames = df[column_name].dropna().tolist()  # Remove NaN values and get list
        # print('-- Total records from Excel:', len(ordered_filenames))

        # Convert Excel file names to actual PDF file names
        pdf_files = []
        missing_files = []

        for f in ordered_filenames:
            sanitized_filename = f.strip().replace("/", "").replace(" ", "") + ".pdf"
            pdf_path = os.path.join(directory, sanitized_filename)

            if os.path.exists(pdf_path):
                pdf_files.append(pdf_path)
            else:
                missing_files.append(sanitized_filename)  # Track missing file

        # print('--------- PDFs found from Excel:', len(pdf_files))
        # print('--------- Missing PDFs:', len(missing_files))

        # Find any remaining PDFs in the directory that were NOT in the Excel list
        all_pdfs_in_directory = {
            os.path.join(directory, file) for file in os.listdir(directory) if file.endswith(".pdf")
        }
        # print('---------all_pdfs_in_directory:', (all_pdfs_in_directory))
        remaining_pdfs = list(all_pdfs_in_directory - set(pdf_files))  # Find PDFs not in the ordered list
        # print('--------- Additional PDFs added at the end:', (remaining_pdfs))

        # Combine both lists: Excel-ordered PDFs first, then remaining ones
        pdf_files.extend(remaining_pdfs)

        temp_files = []
        for i in range(0, len(pdf_files), batch_size):
            pdf_writer = fitz.open()
            batch = pdf_files[i:i + batch_size]

            for pdf_path in batch:
                with fitz.open(pdf_path) as pdf_document:
                    pdf_writer.insertPDF(pdf_document)

            temp_file = f"temp_{i}.pdf"
            pdf_writer.save(temp_file, garbage=6, deflate=True, clean=True, incremental=False)
            pdf_writer.close()
            temp_files.append(temp_file)

        # Merge all temp files
        final_writer = fitz.open()
        for temp_file in temp_files:
            with fitz.open(temp_file) as temp_pdf:
                final_writer.insertPDF(temp_pdf)
            os.remove(temp_file)  # Remove temp files

        final_writer.save(output_file, garbage=6, deflate=True, clean=True, incremental=False)
        final_writer.close()

        # print("PDF merging complete. Output saved at:", output_file)

    # @profile        
    async def run(self, arg_template_id=687, arg_excel_file=r"scube_university_5000.xlsx", arg_directoryUrlForward=r"D:/secq_excel_pdf/script/public/", arg_servername="localhost", arg_db_username="root", arg_password="", arg_dbName="seqr_demo", arg_instance_name="demo",arg_progress_file="386907681128_log.txt", arg_is_generate_live=True):
        global arg_db_username_en, arg_password_en, arg_servername_en, arg_dbName_en
        arg_db_username_en = arg_db_username
        arg_password_en = arg_password
        arg_servername_en = arg_servername
        arg_dbName_en = arg_dbName
        # print(arg_is_generate_live,"__________________________________>")
        # exit()
        # print('-----------------excel 2pdf preview------------------')
        # create_bat_file1("config_tool.bat")
        # execute_bat_file("config_tool.bat")

        # arg_db_username = "root"
        # arg_password = ''

        # arg_servername = "localhost"
        
        print_setbg=''
        arr_content = {} 
        
        rootDir= arg_directoryUrlForward.replace(f'/excel_pdf/', '/')

        mod_dir_for_font = rootDir
        # print(rootDir)
        # print(mod_dir_for_font)
        # exit()
        # image_file_folder = rootDir
        
        try:    
            global connection, cursor                      
            connection = mysql.connector.connect(host=arg_servername_en, database=arg_dbName, user=arg_db_username_en, password=arg_password_en, connection_timeout= 120)

            if connection.is_connected():
                #------------------------------------- DB template_master ------------------------------------------
                db_Info = connection.get_server_info()
                cursor = connection.cursor(buffered=True)
                cursor.execute("select id, template_name, actual_template_name, template_desc, bg_template_id, template_size, width, height, unique_serial_no, site_id, is_block_chain, bc_document_description, bc_document_type, bc_contract_address from template_master where id = '%s'" % (arg_template_id))
                record = cursor.fetchone()
                template_id=record[0]
                template_name=record[1]
                bg_template_id=record[4]
                page_width=record[6]
                page_height=record[7] 
                unique_serial_no = record[8]
                is_block_chain = record[10]
                bc_document_description = record[11]
                bc_document_type = record[12]
                bc_contract_address = record[13]
                
                # ------------------------------------- DB template_master ----------------------------------------- 

                # -------------------------------------- DB fields_master -----------------------------------------
                # cursor.execute("SELECT * FROM fields_master WHERE template_id = '%s'" % (arg_template_id))
                # fields_master_records = cursor.fetchall()

                cursor.execute("select id, template_id, name, mapped_name, security_type, field_position, text_justification, x_pos, y_pos, width, height, font_style, font_id, font_size, font_color, is_font_case, sample_text, sample_image, angle, font_color_extra, line_gap, length, uv_percentage, field_sample_text_width, field_sample_text_vertical_width, field_sample_text_horizontal_width, lock_index, is_repeat, infinite_height, include_image, grey_scale, is_uv_image, is_transparent_image, text_opicity, visible, visible_varification, combo_qr_text, is_mapped,  is_meta_data, meta_data_label, meta_data_value, is_encrypted_qr, encrypted_qr_text, created_at, created_by, updated_at, updated_by from fields_master where template_id = '%s'" % (arg_template_id))
                fields_master_records = cursor.fetchall()
                # -------------------------------------- DB fields_master ----------------------------------------

                # ----------------------------------- set background image ---------------------------------------------

                if bg_template_id!=0:
                    sql_bg="SELECT image_path FROM background_template_master where id= '%s'" % (bg_template_id)
                    cursor.execute(sql_bg)
                    precord = cursor.fetchone()
                    # image_url  = f'https://{arg_instance_name}.seqrdoc.com/{arg_instance_name}/backend/canvas/bg_images/{precord[0]}'
                    image_file_folder_bg = rootDir+arg_instance_name+"/backend/canvas/bg_images"

                    # if not os.path.exists(image_file_folder_bg):
                    #     os.makedirs(image_file_folder_bg)

                    print_bg_file=image_file_folder_bg  + "/" + precord[0] 
                    # print(print_bg_file)
                    # exit()

                    print_setbg='Yes'

                # ----------------------------------- set background image ---------------------------------------------

                # record_unique_id = datetime.datetime.now().strftime('%Y%m%d%H%M%S-') + str(uuid.uuid4()).split('-')[-1]
        
        except Exception as e:
            print("Error while connecting to MySQL", e) 
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Error while connecting to MySQL e1: {e}")   
            print("Connection Issue", f"<b>Error while connecting to Database</b> : <br><p style='color:red;'>{e}</p>")
            error_msg=("Connection Issue", f"<b>Error while connecting to Database</b> : <br><p style='color:red;'>{e}</p>")
            return{
                    "status" : 400,
                    "Message" : "Error 404"f"<b>{error_msg}</b>"
                }
            
        processed_pdfs_folder = rootDir+arg_instance_name+"/processed_pdfs/"                          # D:\omkar_python\seqr_application\public\secura+processed_pdfs/preview
        if not os.path.exists(processed_pdfs_folder):
            os.makedirs(processed_pdfs_folder)

        dirName = rootDir+arg_instance_name+"/documents/" + str(arg_template_id)            # D:\omkar_python\seqr_application\public\pdf2pdf/secura/documents/3
        if not os.path.exists(dirName):
            os.makedirs(dirName)

        folder=rootDir+arg_instance_name+"/documents/" + template_name                         # D:\omkar_python\seqr_application\public\secura\documents\template_name
        if not os.path.exists(folder):
            os.makedirs(folder)

        inner_folder=folder +"/"                                # D:\omkar_python\seqr_application\public\secura\documents\template_name\record_unique_id
        if not os.path.exists(inner_folder):
            os.makedirs(inner_folder)

        datatime_filename = datetime.datetime.now().strftime('%Y%m%d%H%M%S')
        filename = rootDir+arg_instance_name+"/backend/tcpdf/examples/preview/"+str(template_id)+"_"+str(datatime_filename)+".pdf"
        # print(filename)
        # exit()
        
        pdf_file_name = os.path.basename(filename)

        # excel_sheet_name = rootDir+arg_instance_name+'/uploads/excel/'+arg_excel_file
        # excel_sheet_name = rootDir+arg_instance_name+f'/backend/request/uploads/excel/'+arg_excel_file
        excel_sheet_name = arg_excel_file
        df = pd.read_excel(excel_sheet_name, dtype=str)
        
        #-------------------------------- Validate excel -------------------------------------------------------------

        cursor.execute('SELECT DISTINCT(`mapped_name`) FROM `fields_master` where template_id ="%s"' % (arg_template_id))
        excel_heading = cursor.fetchall()
        cleaned_data = [item[0] for item in excel_heading if item[0] is not None]

        header = df.columns.tolist()
        
        diff_in_excel = [item for item in cleaned_data if item not in header]

        if diff_in_excel or df.empty:
            print(f"{datetime.datetime.now().strftime('%d-%m-%Y_%X')} - Exception occurred while validating Excel: {diff_in_excel} field not found in excel.")
            print("Error ", f"<b>Exception occurred while validating excel fields</b> : <br><p style='color:red;'>{diff_in_excel} field not found in excel.</p><b>Note:-</b><br><p>1. Check the spaces in excel column</p><p>2. Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</p><p>3. Name are case sensitive, column sequence insensitive, extra columns are ignored</p><p>Check whether your uploaded excel file contains records or not.</p>")

            return{
                "status" : 400,
                "Message" : "Exception occurred while validating excel fields"
            }
            
        #-------------------------------- Validate excel -------------------------------------------------------------
        tz_IND = pytz.timezone('Asia/Calcutta')
        datetime_IND = datetime.datetime.now(tz_IND) 
        beginning_time = datetime_IND.strftime("%H:%M:%S")
        start_time = time.time()
        # datatime_filename = datetime.datetime.now().strftime('%Y%m%d%H%M%S')

        total_records = df.shape[0]

        doc = fitz.open() 
        
        cnt = 1

        for index, row in df.iterrows():
            try:
                store_serial_no = row[unique_serial_no]
            except:
                print("Exception occurred while validating excel fields")
                return{
                    "status" : 400,
                    "Message" : "Exception occurred while validating excel fields"
                }
            page = doc.newPage(width=await self.mm_to_pixels(page_width, 72), height=await self.mm_to_pixels(page_height, 72))
            page_rect=page.MediaBox
            for placer_data in fields_master_records:
                if not connection.is_connected():
                    self.connection_retries()
                    
                elif placer_data[4]=='Static Image':
                    static = await self.placer_type_static_image(placer_data, arg_instance_name, arg_template_id, rootDir, page, cnt)
                    # print(static)
                    # exit()
                    if isinstance(static, dict):
                        return{
                                "status" : 400,
                                "Message" : static['Message']
                            }
                    
                elif placer_data[4]=='QR Code':
                    QrCode = await self.placer_type_qr_code(placer_data, row, is_block_chain, bc_contract_address, arg_instance_name, dirName, page)
                    if isinstance(QrCode, dict):
                        return{
                                "status" : 400,
                                "Message" : QrCode['Message']
                            }

                elif placer_data[4]=='Micro Text Border':
                    Micro_Text_Border = await self.placer_type_micro_text_border(placer_data, row, page)
                    if isinstance(Micro_Text_Border, dict):
                        return{
                                "status" : 400,
                                "Message" : Micro_Text_Border['Message']
                            }   
                        
                elif placer_data[4]=='Security line':
                    Security_line = await self.placer_type_security_line(placer_data, mod_dir_for_font, row, page)
                    if isinstance(Security_line, dict):
                        return{
                                "status" : 400,
                                "Message" : Security_line['Message']
                            } 

                elif placer_data[4]=='1D Barcode':
                    oneD_Barcode = await self.placer_type_1d_barcode(placer_data, dirName, page)
                    if isinstance(oneD_Barcode, dict):
                        return{
                                "status" : 400,
                                "Message" : oneD_Barcode['Message']
                            } 

                elif placer_data[4]=='Micro line':
                    Micro_line = await self.placer_type_micro_line(placer_data, mod_dir_for_font, row, page)
                    if isinstance(Micro_line, dict):
                        return{
                                "status" : 400,
                                "Message" : Micro_line['Message']
                            } 

                elif placer_data[4]=='2D Barcode':
                    twoD_Barcode = await self.placer_type_2d_barcode(placer_data, row, dirName, page)
                    if isinstance(twoD_Barcode, dict):
                        return{
                                "status" : 400,
                                "Message" : twoD_Barcode['Message']
                            } 
   
                elif placer_data[4]=='Dynamic Image':
                    Dynamic_Image = await self.placer_type_dynamic_image(row, placer_data, arg_instance_name, arg_template_id, template_id, rootDir, cnt, page)
                    if isinstance(Dynamic_Image, dict):
                        return{
                                "status" : 400,
                                "Message" : Dynamic_Image['Message']
                            } 

                elif placer_data[4]=='Normal' or placer_data[4]=='Static Text':
                    Static_Text = await self.placer_type_normal_or_static_text(placer_data, mod_dir_for_font, page, row)
                    if isinstance(Static_Text, dict):
                        return{
                                "status" : 400,
                                "Message" : Static_Text['Message']
                            } 
    
                elif placer_data[4]=='Yellow Patch':
                    Yellow_Patch = await self.placer_type_yellow_patch(mod_dir_for_font, row, placer_data, page)
                    if isinstance(Yellow_Patch, dict):
                        return{
                                "status" : 400,
                                "Message" : Yellow_Patch['Message']
                            } 

                elif placer_data[4]=='Ghost Image':
                    if placer_data[34]==0 or placer_data[34]=='0':
                        Ghost_Image = await self.placer_type_ghost_image(placer_data, row, dirName, page, mod_dir_for_font)
                        if isinstance(Ghost_Image, dict):
                            return{
                                    "status" : 400,
                                    "Message" : Ghost_Image['Message']
                                } 
                        
                elif placer_data[4]=='Anti-Copy':
                    Anti_Copy = await self.placer_type_anti_copy(placer_data, mod_dir_for_font, page)
                    if isinstance(Anti_Copy, dict):
                        return{
                                "status" : 400,
                                "Message" : Anti_Copy['Message']
                            }
            
                elif placer_data[4]=="ID Barcode":
                    if placer_data[34]==0 or placer_data[34]=='0':
                        ID_Barcode = await self.placer_type_id_barcode(placer_data, cnt, dirName, page, mod_dir_for_font)
                        if isinstance(ID_Barcode, dict):
                            return{
                                    "status" : 400,
                                    "Message" : ID_Barcode['Message']
                                } 
                        
            if arg_is_generate_live:

                file_store_dir = os.path.join(rootDir, arg_instance_name+'/', 'backend/pdf_file/', template_name+'_'+str(datatime_filename)+'/')
                # print(file_store_dir)
                # exit()
                pdf_file_path = os.path.join(file_store_dir, f"{store_serial_no}.pdf")
        
                if not os.path.exists(file_store_dir):
                    os.makedirs(file_store_dir)

                await self.save_single_page_as_pdf(pdf_file_path,print_bg_file,page_width,page_height,page)

            for placer_data in fields_master_records:
                if placer_data[4]=='Static Image':
                    Static_Image = await self.placer_type_static_image(placer_data, arg_instance_name, arg_template_id, rootDir, page, cnt,False)
                    if isinstance(Static_Image, dict):
                            return{
                                    "status" : 400,
                                    "Message" : Static_Image['Message']
                                } 

                elif placer_data[4]=='Dynamic Image':
                    Dynamic_Image = await self.placer_type_dynamic_image(row, placer_data, arg_instance_name, arg_template_id, template_id, rootDir, cnt, page,False)
                    if isinstance(Dynamic_Image, dict):
                            return{
                                    "status" : 400,
                                    "Message" : Dynamic_Image['Message']
                                } 

                elif placer_data[4]=='Invisible':
                    Invisible=await self.placer_type_invisible(placer_data, mod_dir_for_font, page, row)
                    if isinstance(Invisible, dict):
                            return{
                                    "status" : 400,
                                    "Message" : Invisible['Message']
                                } 

                elif placer_data[4]=='UV Repeat line':
                    UV_Repeat_line = await self.placer_type_uv_repeat_line(placer_data, mod_dir_for_font, row, page)
                    if isinstance(UV_Repeat_line, dict):
                            return{
                                    "status" : 400,
                                    "Message" : UV_Repeat_line['Message']
                                }
            
                elif placer_data[4]=='UV Repeat Fullpage':  
                    UV_Repeat_Fullpage =await self.placer_type_uv_repeat_full_page(placer_data, mod_dir_for_font, row, page_rect, page)
                    if isinstance(UV_Repeat_Fullpage, dict):
                            return{
                                    "status" : 400,
                                    "Message" : UV_Repeat_Fullpage['Message']
                                }

            if arg_is_generate_live:

                tcpdf_file_store_dir = os.path.join(rootDir, arg_instance_name+"/", 'backend/tcpdf/examples/',template_name+'_'+str(datatime_filename)+'/',)

                tcpdf_file_path = os.path.join(tcpdf_file_store_dir, f"{store_serial_no}.pdf")

                if not os.path.exists(tcpdf_file_store_dir):
                    os.makedirs(tcpdf_file_store_dir)
                    
                await self.save_single_page_as_pdf(tcpdf_file_path,print_bg_file,page_width,page_height,page,True)

            else:

                if print_setbg=='Yes':
                    try: 
                        x1 = 0
                        y1 = 0
                        x2 = await self.mm_to_pixels(page_width, dpi=72)
                        y2 = await self.mm_to_pixels(page_height, dpi=72)
                    
                        page_rect1 = (x1, y1, x2, y2)
                        page.insertImage(page_rect1, print_bg_file,overlay=False)

                    except Exception as e:
                        print(f"Error while setting background image", 
                                                        f"<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>")
                        return{
                                "status" : 400,
                                "Message" : f"Error while setting background image"
                                            f"<p style='color: red;'>Error: {str(e).replace('omkar_python','project_folder')}</p>"
                            }

            # ------- json file (start)------------

            datetime_IND = datetime.datetime.now(tz_IND)
            ending_time = datetime_IND.strftime("%H:%M:%S")
            end_time = time.time() - start_time

            def format_duration(duration):
                seconds = int(duration)
                milliseconds = int((duration - seconds) * 1000)
                return f"{time.strftime('%H:%M:%S', time.gmtime(seconds))}.{milliseconds:03d}"

            seconds_to_hhmmss = format_duration(end_time)
            
            arr_content['percent'] = int((index+1)/total_records * 100)
            arr_content['message'] = "Generating "+str(index+1)+"/"+str(total_records)+" PDF(s)"
            arr_content['beginning_time'] = beginning_time
            arr_content['ending_time'] = ending_time
            arr_content['exec_time'] = end_time
            arr_content['hms_time'] = seconds_to_hhmmss
            arr_content['pages_processed'] = index+1
            # arr_content['printable_pdf'] = tcpdf_file_path
            # arr_content['preview_pdf'] = filename
            # arr_content['printable_pdf'] = printable_pdf_path
            # arr_content['single_preview_pdf'] = file_store_dir
            json_object = json.dumps(arr_content, indent = 4)


            f = open(rootDir+arg_instance_name+'/'+"processed_pdfs/"+arg_progress_file, "w")   
            f.write(json_object)
            # ------- json file (end)------------

            emit_data = {"percent": int(((index+1) / total_records) * 100), "total_pages": total_records, "process_page": index+1, "filename_path":filename}
            
            cnt += 1
            # gc.collect()

        if int(((index+1) / total_records) * 100) == 100:

            if arg_is_generate_live:
                printable_pdf_path = f"{tcpdf_file_store_dir.replace(f'{template_name}_{str(datatime_filename)}/','')}{template_name.replace(' ','_')}_{str(datatime_filename)}.pdf"
                # print("Printable",printable_pdf_path)
                # self.combine_pdfs(tcpdf_file_store_dir, printable_pdf_path)
                self.merge_pdfs(tcpdf_file_store_dir, printable_pdf_path,arg_excel_file,unique_serial_no)
                shutil.rmtree(tcpdf_file_store_dir, ignore_errors=True)
                arr_content['printable_pdf'] = printable_pdf_path
            else:
                # print("----------------->Preview",filename)
                doc.save(filename, garbage=4, deflate=True)

            # print(tcpdf_file_store_dir)
            # os.remove(tcpdf_file_store_dir)
            # shutil.rmtree(tcpdf_file_store_dir, ignore_errors=True)

            shutil.rmtree(dirName, ignore_errors=True)
            datetime_IND = datetime.datetime.now(tz_IND)
            ending_upload_time = datetime_IND.strftime("%H:%M:%S")
            Total_upload_time=time.time() - start_time
            average_time_per_page = Total_upload_time/(cnt-1)
            page_seconds_to_hhmmss = format_duration(average_time_per_page)

            arr_content['ending_upload_time'] = ending_upload_time
            arr_content['Total_upload_time'] = Total_upload_time
            arr_content['exece_time1'] = format_duration(Total_upload_time)
            arr_content['page_time'] = page_seconds_to_hhmmss
            # arr_content['printable_pdf'] = printable_pdf_path
            
            
            cursor.close()
            doc.close()
            connection.close()
            del connection, cursor, arr_content

            if arg_is_generate_live:
                return{
                    "status" : 200,
                    "Message" : f"Sucess :{emit_data['percent']}%",
                    "Printable": printable_pdf_path,
                    "Preview_Single" : file_store_dir,
                    "Log_File" : rootDir+arg_instance_name+"/"+"processed_pdfs/"+arg_progress_file
                }
            else:
                return{
                    "status" : 200,
                    "Message" : f"Sucess :{emit_data['percent']}%",
                    "Preview_Big" : filename,
                    "Log_File" : rootDir+arg_instance_name+"/"+"processed_pdfs/"+arg_progress_file
                }
        else:
            return{
                "status" : 400,
                "Message" : f"Incomplete :{emit_data['percent']}",
            }        
            
    def connection_retries(self):
        global connection, cursor
        retries=5
        delay=10
        attempt = 0
        connection = None
        while attempt < retries:
            try:
                connection = mysql.connector.connect(
                    host=arg_servername_en,
                    database=arg_dbName_en,
                    user=arg_db_username_en,
                    password=arg_password_en,
                    connection_timeout=10  
                )
                if connection.is_connected():
                    print("Connection successful")
                    cursor = connection.cursor(buffered=True)
                    break
                    
            except mysql.connector.Error as err:
                print(f"Connection failed: {err}")
                attempt += 1
                print(f"Retrying {attempt}/{retries} in {delay} seconds...")
                
                time.sleep(delay)
        print("All retries failed. Exiting.")

def generate_pdf(arg_template_id, arg_excel_file, arg_directoryUrlForward, arg_servername, arg_db_username, arg_password, arg_dbName, arg_instance_name, arg_progress_file,arg_is_generate_live):
    generate_instance = PDFGenerator()

    output = asyncio.run(generate_instance.run(arg_template_id, arg_excel_file, arg_directoryUrlForward, arg_servername, arg_db_username, arg_password, arg_dbName, arg_instance_name,arg_progress_file,arg_is_generate_live))

    return output

# start_time = time.time()  

# print(generate_pdf(742,r"D:\secq_excel_pdf\Desktop utility testing.xlsx","D:/secq_excel_pdf/script/public/excel_pdf/","localhost","root","","seqr_demo","demo","Log_Desktop_utility_testing.txt",False))
# # print(generate_pdf(742,r"D:/secq_excel_pdf/stream_check.xlsx","D:/secq_excel_pdf/script/public/excel_pdf/","localhost","root","","seqr_demo","demo","Log_Desktop_utility_testing.txt",False))

# # generate_pdf(687,r"D:\secq_excel_pdf\scube_university_5000.xlsx","D:/secq_excel_pdf/script/public/excel_pdf/","localhost","root","","seqr_demo","demo",)

# execution_time = time.time() - start_time  
# print(f"Execution time: {execution_time:.2f} seconds")