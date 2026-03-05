#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe
from __future__ import print_function
import os, PyPDF2
import fitz
import sys

#pdf2merge = []
filename=sys.argv[1];
filename2=sys.argv[2];
merge_result_path=sys.argv[3];
bg_path=sys.argv[4];

source_path="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/backend/tcpdf/examples/";
#merge_result_path="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/estamp/backend/test_pdf/";
#merge_result_path="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/estamp/backend/tcpdf/examples/";
#pdf2merge.append(filename)
doc1 = fitz.open(bg_path)
page = doc1.loadPage(0)
page_front = fitz.open()
doc2 = fitz.open(source_path+filename)
#print(doc2)
page_front.insertPDF(doc2, from_page=0, to_page=0)
page.showPDFpage(page.rect, page_front, pno=0, keep_proportion=True, overlay=True, rotate=0, clip=None)
#doc1.save(merge_result_path+filename2, encryption=fitz.PDF_ENCRYPT_KEEP)
doc1.save(merge_result_path+filename2, encryption=fitz.PDF_ENCRYPT_KEEP, garbage=4, deflate=True)
