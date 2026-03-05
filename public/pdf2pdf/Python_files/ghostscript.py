#!C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe


from __future__ import print_function
import os
import sys
import subprocess
 
print(sys.argv[1])
print(sys.argv[2]) 
#for root, dirs, files in os.walk("C:\\Users\\Administrator\\Desktop\\testcompression"):
#    for file in files:
#        if file.endswith(".pdf"):
#            filename = os.path.join(root, file)
#           print (filename)
#            arg1= '-sOutputFile=' + "c" +  file #added a c to the filename
#            p = subprocess.Popen(['C:/Program Files/gs/gs9.56.1/bin/gswin64c.exe',
#                                  '-sDEVICE=pdfwrite',
#                                  '-dCompatibilityLevel=1.4',
#                                 '-dPDFSETTINGS=/screen', '-dNOPAUSE', ebook 
#                                  '-dBATCH', '-dQUIET', str(arg1), filename],
#                                 stdout=subprocess.PIPE)
#            print (p.communicate())
#%ghostscript% -q -dNOPAUSE -dBATCH -dSAFER -dSimulateOverprint=true -sDEVICE=pdfwrite 
#-dPDFSETTINGS=/ebook -dEmbedAllFonts=true -dSubsetFonts=true -dAutoRotatePages=/None 
#-dColorImageDownsampleType=/Bicubic -dColorImageResolution=150 -dGrayImageDownsampleType=/Bicubic 
#-dGrayImageResolution=150 -dMonoImageDownsampleType=/Bicubic -dMonoImageResolution=150 -sOutputFile=output.pdf input.pdf

# sourceFile = sys.argv[1]
# outputFile = sys.argv[2]
# arg1= '-sOutputFile=' + outputFile
# p = subprocess.Popen(['C:/Program Files/gs/gs9.56.1/bin/gswin64c.exe',
#                       '-sDEVICE=pdfwrite',
#                       '-dCompatibilityLevel=1.4',
#                       '-dPDFSETTINGS=/ebook', '-dNOPAUSE',
#                       '-dBATCH', '-dQUIET', str(arg1), sourceFile],
#                      stdout=subprocess.PIPE)
# print (p.communicate())


sourceFile = sys.argv[1]
outputFile = sys.argv[2]
arg1= '-sOutputFile=' + outputFile
p = subprocess.Popen(['C:/Program Files/gs/gs9.56.1/bin/gswin64c.exe',
                      '-q','-dNOPAUSE','-dBATCH','-dSAFER','-dSimulateOverprint=true','-sDEVICE=pdfwrite',
                      '-dPDFSETTINGS=/ebook','-dEmbedAllFonts=true','-dSubsetFonts=true','-dAutoRotatePages=/None',
                      '-dColorImageDownsampleType=/Bicubic','-dColorImageResolution=150','-dGrayImageDownsampleType=/Bicubic',
                      '-dGrayImageResolution=150','-dMonoImageDownsampleType=/Bicubic','-dMonoImageResolution=150', str(arg1), sourceFile],
                     stdout=subprocess.PIPE)
print (p.communicate())