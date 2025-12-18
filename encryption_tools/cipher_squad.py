"""
In command line, run "pip install spylls" to install the library
"""
from spylls.hunspell import Dictionary
from math import sqrt

# Common functions
def gcd(a,b):
    """
    Finds the greatest common denomenator between two numbers. Can be used to cack if they are coprimes
    """
    if(b == 0):
        return a
    else:
        return gcd(b, a % b)

def modulo_inverse(a: int,m: int) -> int:
    """
    Returns i where (i*a)%m = 1
    if a and m are not coprimes, returns none 
    """
    for i in range(1,m):
        if (a*i)%m==1:
            return i
    return None


# Private encryption
def count_letters(phrase:str)->dict:
    counts = {}
    words = phrase.upper().split()
    for word in words:
        for letter in word:
            if letter in counts:
                counts[letter] += 1
            else:
                counts[letter] = 1
    return counts

def encrypt(phrase: str,  a: int, b: int) -> str:
    """
    Encrypts a phrase using an affine shift of the form (ap+b) mod 26 where p is the numerical representation of the letter - 1,
    a and 26 must be coprimes
    """
    if gcd(a,26) != 1:
        return "The given key is not usable, please input a valid key as 'a' and 26 must be coprimes"

    encrypted =  ""
    for letter in phrase:
        # Encrypt only letters and ignore symbols ad spaces
        if letter.isalpha():
            p = ord(letter.upper())-65
            c = (a*p + b)%26
            encrypted += chr(c+65)
        else:
            encrypted += letter
    return encrypted

def decrypt(phrase: str,  a: int, b: int) -> str:
    """
    Decrypts a phrase using an affine shift of the form (a^-1)*(p-b) mod 26 where p is the numerical representation of the letter - 1,
    a and 26 must be coprimes
    """
    if gcd(a,26) != 1:
        return "Invalid key"

    decrypted = ""
    a_inverse = modulo_inverse(a,26)
    # Decrypts only letters and ignors symbols and spaces
    for letter in phrase:
        if letter.isalpha():
            p = ord(letter.upper())-65
            c = (a_inverse*(p-b))%26
            decrypted += chr(c+65)
        else:
            decrypted += letter
    return decrypted

def force_decrypt(phrase: str, mode: int) -> list:
    """
    Since a and 26 must be coprimes, loop through all coprimes of 26 less than 26 because numbers larger than 26 
    will have a modulo within the given list and all numbers between 0 and 25 for the possible shifts.

    A forced english affine decryption has 312 possible values because 12 coprimes * 26 possible shifts

    Mode 1 gives all 312 possible decryptions
    Mode 2 gives only those with valid english words
    Mode 3 gives only those that start with valid first or last english words
    Mode 4 returns 9 possible decryptions using the letters that appear the most in the english language
    """
    options = []
    valid_words = Dictionary.from_files("en_US")

    # Possible coprimes of 26
    for a in [1,3,5,7,9,11,15,17,19,21,23,25]:
        # possible shifts
        for b in range(0,26):
            # Loops through all possible combinations and uses them as keys for decryption
            possible = decrypt(phrase,a,b)
            # Skips invalid keys
            if possible == "Invalid key":
                continue
            # Returns all possible decryptions
            if mode == 1:
                options.append(possible)
            # Selects only decryptions where everyword is in the english alphabet
            elif mode == 2:
                flag = True
                for word in possible.split():
                    # Deconstructs and reconstructs words to only include letters and no symbols
                    letters = [character for character in word if character.isalpha()]
                    new_word = "".join(letters)
                    flag = valid_words.lookup(new_word)
                    if flag == False:
                        break
                # If all words are in the dictionary then append them to the list of possible decryptions
                if flag:
                    options.append(possible)
            elif mode ==3:
                words = possible.split()
                if valid_words.lookup(words[0]) or valid_words.lookup(words[-1]):
                    options.append(possible)
            elif mode == 4:
                most_appearing_letters = {"E","T","A","O","I","N","S","H","R"}
                frequencies = count_letters(phrase)
                highest_freq_letter = max(frequencies, key = lambda x: frequencies[x])
                if decrypt(highest_freq_letter,a,b) in most_appearing_letters:
                    options.append(possible)
            else:
                print("Invalid Mode")
                return None
    # If list is not empty return the list
    if len(options) > 0:
        return options
    else:
        return "No valid english decryptions found"


# Public ecryption
def is_prime(n: int) -> bool:
    """
    Checks whether a number is a prime number
    """
    # Handles cases for 0 and 1
    if n < 2:
        return False
    # Checks for fators of n, if present return false
    for i in range(2,n//2):
        if n%i == 0:
            return False
    return True

def prime_factors(n:int) -> list:
    factors = []

    # Divide by 2 until the number is odd
    while n%2 == 0:
        factors.append(2)
        n //= 2

    # Since n must be odd here count in steps of 2s
    for i in range(3,int(sqrt(n))+1,2):
        if n%i == 0:
            factors.append(i)
            n //= i

    # if n ends on a prime number, add to the list
    if n > 2:
        factors.append(n)

    return factors

def Φ(n: int) -> int:
    """
    Returns Euler's Totient for n, the number of positive integers coprime with n
    Found by 
    """
    number = n
    factors = prime_factors(n)
    distinct_prime_factors = list(set(factors))
    distinct_prime_factors.sort()
    m = len(distinct_prime_factors)
    for i in range(0,m):
        number *= (1-(1/distinct_prime_factors[i]))
    return int(number)

def is_valid_key(n: int, e: int) -> bool:
    if gcd(Φ(n),e) == 1:
        return True
    else:
        return False
    
def text_to_num(text: str) -> list[str]:
    numbers = []
    if len(text)%2 == 1:
        text += " "
    for letter in text:
        num = ord(letter) - 32
        if num < 10:
            numbers.append("0"+str(num))
        else:
            numbers.append(str(num))
    return numbers

def make_number_blocks(numbers: list[str]) -> list[int]:
    number_blocks = []
    for i in range(0,len(numbers)-1,2):
        part1 = numbers[i]
        part2 = numbers[i+1]
        number_blocks.append(int(part1+part2))
    return number_blocks

def rsa_encrypt(plain_blocks: list[int], n: int, e: int) -> list[str]:
    encrypted_blocks = []
    for block in plain_blocks:
        cipher = (block**e)%n
        encrypted_blocks.append(cipher)
    return encrypted_blocks

def rsa_decrypt(cipher_blocks: list[int], n: int, e: int) -> list[int]:
    d = modulo_inverse(e,Φ(n))
    plain_blocks = []
    for block in cipher_blocks:
        plain = (block**d)%n
        plain_blocks.append(plain)
    return plain_blocks

def convert_number_blocks_to_text(plain_blocks: list[int]) -> str:
    letters = []
    for block in plain_blocks:
        block = str(block).zfill(4)
        letter1_numbers = int(block[:2])
        letter2_numbers = int(block[2:])
        letters.append(chr(letter1_numbers+32))
        letters.append(chr(letter2_numbers+32))
    phrase = "".join(letters)
    return phrase

def complete_rsa_encryption(plain_text: str, n: int, e: int):
    number_equivalents = text_to_num(plain_text)
    number_equivalents_in_blocks = make_number_blocks(number_equivalents)
    encrypted_text =  rsa_encrypt(number_equivalents_in_blocks, n, e)
    return " ".join(map(str,encrypted_text))

def complete_rsa_decryption(cipher_text: str, n: int, e: int):
    list_of_cipher_text = map(int,cipher_text.split())
    plain_text_in_number_blocks = rsa_decrypt(list_of_cipher_text, n, e)
    plain_text = convert_number_blocks_to_text(plain_text_in_number_blocks)
    return plain_text