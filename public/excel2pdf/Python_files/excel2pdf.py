#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe

# Import Module
from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
import os
from win32com import client


# Open Microsoft Excel
excel = client.Dispatch("Excel.Application")
#excel = client.gencache.EnsureDispatch('Excel.Application')

print("abc")
# Read Excel File
sheets = excel.Workbooks.Open('C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\pdf2pdf\\Python_files\\UV TA Format.xlsx')

work_sheets = sheets.Worksheets[0]

# Convert into PDF File
work_sheets.ExportAsFixedFormat(0, 'C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\pdf2pdf\\Python_files\\Excel.pdf')
