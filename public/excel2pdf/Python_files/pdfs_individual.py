#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe
from PyPDF2 import PdfFileWriter, PdfFileReader

inputpdf = PdfFileReader(open("C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/auro/AURO_C20221215041223_with_bg.pdf", "rb"))
cnt=355
for i in range(inputpdf.numPages):
    output = PdfFileWriter()
    output.addPage(inputpdf.getPage(i))
    cnt += 1
    with open("C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/auro/single/GUI%s.pdf" % cnt, "wb") as outputStream:
        output.write(outputStream)