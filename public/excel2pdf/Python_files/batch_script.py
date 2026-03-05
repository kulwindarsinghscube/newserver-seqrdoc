import pandas as pd
import fitz
import sys
# sys.path.append("C:\wamp64\www\seqr\public\excel_pdf\Python_files\new_env_excel_pdf\Scripts\python.exe ")
sys.path.append("C:\\inetpub\\vhosts\\seqrdoc.com\\httpdocs\Python\\Python3.8.10\env\\Scripts\\python.exe ")

import datetime
import os
from excel_pdf_script import generate_pdf
import shutil
import time
import json

# Configurations
arg_template_id = sys.argv[1]
# arg_template_id = 742
# arg_excel_file = r"D:\secq_excel_pdf\scube_university_5000.xlsx"
arg_excel_file = sys.argv[2]
# arg_excel_file = r"C:\wamp64\www\seqr\public\excel_pdf\Python_files\Desktop utility testing.xlsx"
arg_directoryUrlForward = sys.argv[3]
# arg_directoryUrlForward = r"C:/wamp64\www/seqr/public/excel_pdf/"
arg_servername = sys.argv[4]
# arg_servername = "localhost"
arg_db_username = sys.argv[5]
# arg_db_username = "root"
arg_password = sys.argv[6]
# arg_password = ""
arg_dbName = sys.argv[7]
# arg_dbName = "seqr_demo1"
arg_instance_name = sys.argv[8]
# arg_instance_name = "demo"
arg_progress_file = "Log_Desktop_utility_testing.txt"

arg_is_generate_live = sys.argv[9].lower() == "true"
# arg_is_generate_live = False
# arg_is_generate_live = True


rootDir= arg_directoryUrlForward.replace(f'/excel_pdf/', '/')
datatime_filename = datetime.datetime.now().strftime('%Y%m%d%H%M%S')


def move_pdfs_to_single_folder(directories, target_folder):
    # Ensure the target folder exists, if not, create it
    if not os.path.exists(target_folder):
        os.makedirs(target_folder)  

    for directory in directories:
        # Check if the directory exists
        if os.path.exists(directory):
            # Iterate through all files in the directory
            for filename in os.listdir(directory):
                # Only move .pdf files
                if filename.endswith(".pdf"):
                    source_file = os.path.join(directory, filename)
                    target_file = os.path.join(target_folder, filename)

                    # Move the PDF to the target folder
                    shutil.move(source_file, target_file)
                    # print(f"Moved: {filename} from {directory} to {target_folder}")
        else:
            print(f"Directory not found: {directory}")


        # After moving all PDFs, remove the empty directory
        try:
            os.rmdir(directory)  # Only works if the directory is empty
            # print(f"Removed directory: {directory}")
        except OSError:
            # Use shutil.rmtree() to remove non-empty directories
            shutil.rmtree(directory)
            # print(f"Removed non-empty directory: {directory}")

def merge_pdfs(pdf_files, output_file, batch_size=1000): 
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
        os.remove(temp_file)  # Optionally remove temp files

    final_writer.save(output_file, garbage=6, deflate=True, clean=True, incremental=False)
    final_writer.close()

    # Remove original PDFs
    for pdf in pdf_files:
        if os.path.exists(pdf):
            os.remove(pdf)

def process_batch(batch_df, batch_number):
    """Process a batch of records and return result paths."""
    temp_excel = f"temp_batch_{batch_number}.xlsx"
    batch_df.to_excel(temp_excel, index=False)  
    
    # print(f"Processing batch {batch_number}...")

    # Run generate_pdf
    result = generate_pdf(arg_template_id, temp_excel, arg_directoryUrlForward,
                          arg_servername, arg_db_username, arg_password,
                          arg_dbName, arg_instance_name, arg_progress_file, arg_is_generate_live)
    
    # print(f"Batch {batch_number} completed.")

    os.remove(temp_excel) 

    return result

def main():

    arr_content = {} 

    arr_content['percent'] = '0 %'
 
    # batch_progress_log_file = rootDir+arg_instance_name+"/processed_pdfs/"+str(arg_template_id)+".txt"
    # batch_progress_log_file = os.path.join(rootDir, arg_instance_name, "processed_pdfs", f"{str(arg_template_id)}.txt")
    batch_progress_log_file = os.path.join(rootDir, arg_instance_name, "processed_pdfs", f"Log_Desktop_utility_testing.txt")

    # Check if the file exists; if not, create it and write arr_content in JSON format
    if not os.path.exists(batch_progress_log_file):
        os.makedirs(os.path.dirname(batch_progress_log_file), exist_ok=True)

    # Open the file and write arr_content in JSON format
    with open(batch_progress_log_file, 'w') as log_file:
        json.dump(arr_content, log_file, indent=4)

    """Main function to process the Excel file in batches."""
    df = pd.read_excel(arg_excel_file)
    # Split into batches
    batch_size = 50
    
    batches = [df[i:i+batch_size] for i in range(0, len(df), batch_size)]

    # Separate lists for paths
    printable_paths = []
    preview_big_paths = []
    preview_single_paths = []

    for batch_number, batch in enumerate(batches, start=1):
        result = process_batch(batch, batch_number)
        
        try:
            # print(result)
            # exit()
            # Extract paths and store them in separate lists
            if result and "status" in result and result["status"] == 200:

                if arg_is_generate_live:
                    printable_paths.append(result.get("Printable", ""))
                    preview_single_paths.append(result.get("Preview_Single", ""))
                else:
                    preview_big_paths.append(result.get("Preview_Big", ""))


            elif result["status"] == 400:
                return{
                    "status" : 400,
                    "Message" : result["Message"]
                }
                
            percentage_complete = (batch_number / len(batches)) * 100
            # print("------------------------------"+str(percentage_complete)+" % COMPLETED-------------------------------------------")
            
            arr_content['percent'] = f'{percentage_complete} %'

            # Open the file and write arr_content in JSON format
            with open(batch_progress_log_file, 'w') as log_file:
                json.dump(arr_content, log_file, indent=4)

            # return{
            #         "status" : result["status"],
            #         "Message" : f"Sucess :{percentage_complete}%",
            #     }
        except Exception as e:
            return{
                    "status" : 400,
                    "Message" : e
                }
        
    # print("\nAll Generated File Paths:")
    # print("Printable PDFs:", printable_paths)
    # print("Preview Big PDFs:", preview_big_paths)
    # print("Preview Single PDFs:", preview_single_paths)
    if arg_is_generate_live:
        printable_filename = rootDir+arg_instance_name+"/backend/tcpdf/examples/"+str(arg_template_id)+"_"+str(datatime_filename)+".pdf"

        try:
            # print("----------------->",printable_paths)
            merge_pdfs(printable_paths, printable_filename)
            preview_single_filename = os.path.join(rootDir, arg_instance_name+'/', 'backend/pdf_file/', str(arg_template_id)+'_'+str(datatime_filename)+'/')
            move_pdfs_to_single_folder(preview_single_paths, preview_single_filename)
    
            return  {
            "status" : 200,
            "Message" : f"Sucess :{percentage_complete}%",
            "Printable": printable_filename,
            "Preview_Single" : preview_single_filename,
            }
        
        except Exception as e:
            return  {
                "status" : 400,
                "Message" : e
            }

    else:
        preview_big_filename = rootDir+arg_instance_name+"/backend/tcpdf/examples/preview/"+str(arg_template_id)+"_"+str(datatime_filename)+"P.pdf"

        try:
            # print("called------------------------------------------>",preview_big_paths)
            merge_pdfs(preview_big_paths, preview_big_filename)

            return  {
                "status" : 200,
                "Message" : f"Sucess :{percentage_complete}%",
                "Preview_Big" : preview_big_filename,
            }
        except Exception as e:
            return  {
                "status" : 400,
                "Message" : e
            }


# Run the function
if __name__ == "__main__":
    start_time = time.time() 
    w = main()
    print(w)

    execution_time = time.time() - start_time  
    print(f"Execution time: {execution_time:.2f} seconds")