#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe
from __future__ import print_function
import os, PyPDF2
import fitz
import sys
import argparse
import os.path
import shutil
import subprocess


#pdf2merge = []
filename=sys.argv[1];
filename2=sys.argv[2];
merge_result_path=sys.argv[3];
bg_path=sys.argv[4];


def compress(input_file_path, output_file_path, power=0):
    """Function to compress PDF via Ghostscript command line interface"""
    quality = {0: "/default", 1: "/prepress", 2: "/printer", 3: "/ebook", 4: "/screen"}

    # Basic controls
    # Check if valid path
    if not os.path.isfile(input_file_path):
        print("Error: invalid path for input PDF file.", input_file_path)
        sys.exit(1)

    # Check if file is a PDF by extension
    if input_file_path.split('.')[-1].lower() != 'pdf':
        print(f"Error: input file is not a PDF.", input_file_path)
        sys.exit(1)

    gs = get_ghostscript_path()
    #print("Compress PDF...")
    initial_size = os.path.getsize(input_file_path)
    subprocess.call(
        [
            gs,
            "-sDEVICE=pdfwrite",
            "-dJPEGQ=20"
            "-r600",
            "-dCompatibilityLevel=1.4",
            "-dPDFSETTINGS={}".format(quality[power]),
            "-dDownsampleColorImages=false",
            "-dColorImageResolution=150",
            "-dNOPAUSE",
            "-dQUIET",
            "-dBATCH",
            "-sOutputFile={}".format(output_file_path),
            input_file_path,
        ]
    )
    #final_size = os.path.getsize(output_file_path)
    #ratio = 1 - (final_size / initial_size)
    #print("Compression by {0:.0%}.".format(ratio))
    #print("Final file size is {0:.5f}MB".format(final_size / 1000000))
    #print("Done.")


def get_ghostscript_path():
    gs_names = ["gs", "gswin32", "gswin64"]
    for name in gs_names:
        if shutil.which(name):
            return shutil.which(name)
    raise FileNotFoundError(
        f"No GhostScript executable was found on path ({'/'.join(gs_names)})"
    )


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
doc1.save(merge_result_path+"uc_"+filename2, encryption=fitz.PDF_ENCRYPT_KEEP, garbage=4, deflate=True)

compress(merge_result_path+"uc_"+filename2, merge_result_path+filename2, power=2)

