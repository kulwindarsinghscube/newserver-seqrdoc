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
from urllib.parse import unquote
from boto3.s3.transfer import S3Transfer
import boto3

tz_IND = pytz.timezone('Asia/Calcutta') 

blockchain_response_refresh = requests.get('https://mainnet-apis.herokuapp.com/v1/mainnet/verifyWeb3Session')
print(blockchain_response_refresh)
print("True")