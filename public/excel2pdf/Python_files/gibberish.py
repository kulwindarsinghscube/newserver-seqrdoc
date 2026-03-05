from __future__ import print_function
from operator import itemgetter
from itertools import groupby
import sys
sys.path.append("F:\\pdf2pdf_env\\Lib\\site-packages")
import os
import base64
import hashlib
import Crypto
from Crypto.Cipher import AES
import Crypto.Random
import Crypto.Util.Padding

from pypdf import PdfReader

"""
Mimic the Gibberish-AES JS library API. All constants are defaulted for AES-256.

Adapting the script for AES-128 et AES-192 should be relatively straightforward.
"""

def rawEncrypt(plaintext: bytes, key: bytes, iv: bytes) -> bytes:
    """Return the padded cipher text with the default blocksize of 16."""
    return Crypto.Cipher.AES.new(key, Crypto.Cipher.AES.MODE_CBC, iv=iv).encrypt(
        Crypto.Util.Padding.pad(plaintext, 16)
    )


def rawDecrypt(ciphertext: bytes, key: bytes, iv: bytes) -> bytes:
    return Crypto.Util.Padding.unpad(
        Crypto.Cipher.AES.new(key, Crypto.Cipher.AES.MODE_CBC, iv=iv).decrypt(ciphertext),
        16
    )


def openSSLKey(password: str, salt: bytes):
    salted_password = password.encode('utf-8') + salt
    hash_1 = hashlib.md5(salted_password).digest()
    hash_2 = hashlib.md5(hash_1 + salted_password).digest()
    hash_3 = hashlib.md5(hash_2 + salted_password).digest()
    return (hash_1 +  hash_2, hash_3)


def enc(plaintext: str, password: str):
    salt = Crypto.Random.get_random_bytes(8)
    return base64.b64encode(
        b'Salted__' + salt + rawEncrypt(plaintext.encode('utf-8'), *openSSLKey(password, salt))
    )


def dec(ciphertext: str, password: str):
    ciphertext = base64.b64decode(ciphertext)
    salt = ciphertext[8:16]
    ciphertext = ciphertext[16:]
    return rawDecrypt(ciphertext, *openSSLKey(password, salt))



pdfFile = "E:\\wamp64\\www\\uneb\\public\\demo\\backend\\pdf_file\\GUID0001.pdf"
#f = open(pdfFile, "r")

reader = PdfReader(pdfFile)
text = ""
for page in reader.pages:
    text += page.extract_text() + "\n"

print(text)

#print(f.read())
#file = open(pdfFile, "rb")

#print(file.read())

#input_text = file.read()
#plaintext = file.read()
#print(plaintext)
#plaintext = '1 | TE-BR-1234567890 | 2023-02-24T14:45:57Z | Narayana Reddy | Narayan Reddy S/H/O Velugonda Reddy, Flat 501, Garudadri Height, Road No: 8C, Bandari | 100000 (One Lac Only)'
# DescryptedStr = dec(plaintext,'AJITNATH')
# print(DescryptedStr)
plaintext =""

encryptedStr = enc(plaintext,'sqRuD5WYw5wd0rdHR9yLlM6wt2vteuiniQBqE70nAuhU=')

encryptedStr = dec(plaintext,'sqRuD5WYw5wd0rdHR9yLlM6wt2vteuiniQBqE70nAuhU=')


print(encryptedStr)
