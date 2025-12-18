import sys, cipher_squad

# Format for calling program:
# argv[0] = filename
# argv[1] = Mode (Encrypt/decrypt): e for encrypt, d for decrypt
# argv[2] = text to encrypt/decrypt
# argv[3] = n
# argv[4] = e

if __name__ == "__main__":
    mode = sys.argv[1]
    if (mode == "e"):
        to_encrypt = sys.argv[2]
        n = int(sys.argv[3])
        e = int(sys.argv[4])
        result = cipher_squad.complete_rsa_encryption(to_encrypt, n, e)
    elif (mode == "d"):
        to_decrypt = sys.argv[2]
        n = int(sys.argv[3])
        e = int(sys.argv[4])
        if cipher_squad.is_valid_key(n, e):
            result = cipher_squad.complete_rsa_decryption(to_decrypt, n, e)
        else:
            result = "Invalid Key " + str(n) + " " + str(e)
    else:
        result = "Invalid Mode"

    print(result, end='')