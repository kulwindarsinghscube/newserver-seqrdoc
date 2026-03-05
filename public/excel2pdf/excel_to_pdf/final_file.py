#!C:/Users/Administrator/AppData/Local/Programs/Python/Python38/python.exe
import subprocess
import os
import sys
from PyPDF2 import PdfReader, PdfWriter
import datetime

def convert_xlsm_to_pdf(soffice_path, input_file, page_type):
    try:
        # Define the temporary PDF file path with timestamp
        timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
        temp_pdf_file = os.path.splitext(input_file)[0] + f'_{timestamp}.pdf'
        temp_pdf_dir = os.path.dirname(temp_pdf_file)
    
        #print(temp_pdf_dir)
        # Ensure the temporary directory exists
        if not os.path.exists(temp_pdf_dir):
            os.makedirs(temp_pdf_dir)
        
       
        #soffice_path = soffice_path.replace(' ', '\\ ')
        # Convert the XLSM file to PDF using LibreOffice's soffice command
        command = [
            soffice_path,
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            temp_pdf_dir,
            input_file
        ]
        subprocess.run(command, check=True)
        #print(f"Converted {input_file} to {temp_pdf_file} successfully.")
        
        # Rename the converted PDF file to a new name
        new_temp_pdf_file = os.path.join(temp_pdf_dir, f"{os.path.splitext(os.path.basename(input_file))[0]}.pdf")
        os.rename(new_temp_pdf_file, temp_pdf_file)
        #print(f"Renamed temporary file to {new_temp_pdf_file}.")

        filePath = temp_pdf_file

        if page_type == "sing":
            # Extract the first page
            #output_pdf_file = str(datetime.datetime.now().strftime("%d%m%Y_%H%M%S_")) + os.path.basename(new_temp_pdf_file)
            timestamp1 = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
            output_pdf_file = os.path.splitext(temp_pdf_file)[0] + f'single.pdf'

            extract_first_page(temp_pdf_file, output_pdf_file)
            #print(output_pdf_file)
            # Clean up temporary PDF file
            if os.path.exists(temp_pdf_file):
                os.remove(temp_pdf_file)
                #print(f"Temporary file {temp_pdf_file} deleted successfully.")
            filePath = output_pdf_file 
            # print(output_pdf_file)

        print(filePath)

    except subprocess.CalledProcessError as e:
        print(f"Failed to convert {input_file} to PDF. Error: {e}")

def extract_first_page(input_pdf, output_pdf):
    try:
        pdf_reader = PdfReader(input_pdf)
        pdf_writer = PdfWriter()

        # Get the first page
        first_page = pdf_reader.pages[0]
        pdf_writer.add_page(first_page)

        # Write the first page to a new PDF
        with open(output_pdf, 'wb') as out_file:
            pdf_writer.write(out_file)

        # print(f"Extracted first page to {output_pdf} successfully.")
    except Exception as e:
        print(f"Failed to extract first page. Error: {e}")

if __name__ == "__main__":

    # print(sys.argv)
    if len(sys.argv) != 4:
        print("Usage: python script.py <soffice_path> <input_file> <page_type>")
        sys.exit(1)

    soffice_path = sys.argv[1]
    input_file = sys.argv[2]
    page_type = sys.argv[3]
    
    # soffice_path = soffice_path.replace(' ', '\\ ')
    # print(soffice_path)
    if not os.path.isfile(input_file):
        print(f"Error: The file {input_file} does not exist.")
        sys.exit(1)
    
    convert_xlsm_to_pdf(soffice_path, input_file, page_type)
