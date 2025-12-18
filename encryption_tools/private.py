import sys, cipher_squad

# Format for calling program:
# argv[0] = filename
# argv[1] = Mode (Encrypt/decrypt): e for encrypt, d for decrypt

# If mode is e:
# argv[2] = text to encrypt
# argv[3] = slope/gradient of affine shift (a)
# argv[4] = shift/intercept of affine shift (b)

# If mode is d:
# argv[2] = text to decrypt
# argv[3] = slope/gradient of affine shift (a)
# argv[4] = shift/intercept of affine shift (b)

if __name__ == "__main__":
    mode = sys.argv[1]
    if (mode == "e"):
        to_encrypt = sys.argv[2]
        gradient = int(sys.argv[3])
        shift = int(sys.argv[4])
        result = cipher_squad.encrypt(to_encrypt, gradient, shift)
    elif (mode == "d"):
        to_decrypt = sys.argv[2]
        gradient = int(sys.argv[3])
        shift = int(sys.argv[4])
        result = cipher_squad.decrypt(to_decrypt, gradient, shift)
    else:
        result = "Invalid Mode"

    print(result)