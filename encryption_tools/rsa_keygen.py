import cipher_squad, primePy.primes as primes
from random import randint

if __name__ == "__main__":
    possible_primes =  primes.between(100,999)
    p = possible_primes[randint(0,len(possible_primes)-1)]
    q = possible_primes[randint(0,len(possible_primes)-1)]
    n = p*q
    e = 2
    while cipher_squad.gcd(e,cipher_squad.Φ(n)) != 1:
        e +=1

    print(str(n)+"," + str(e))