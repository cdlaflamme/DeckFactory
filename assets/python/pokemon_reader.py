#pokemon_reader.py
#encoding: utf-8

#given a pokemon deck text file path, reads the cards in the deck, and creates a JSON file that can represent the deck in Tabletop Simulator.
#Creates seperate decks for tokens and double faced-versions of double-faced cards (if needed).
#Does not currently allow for the printing to be selected. TODO determine which printing is used

# ======= imports ============
import pokemontcgsdk as tcg
import re #regex
import time #for sleeping to be polite
import sys #command line arguments

# ====== constants =============
API_KEY = '741b9f2c-5344-4e50-8acd-72186cffc6bf' #please don't post or share this anywhere
POLITENESS_DELAY = 0.1 #scryfall has nicely asked me to wait 100ms between requests; ill do the same for pokemontcg
X_SPACE =  2.5  #padding between decks in the X direction, in unknown units. experimentally determined to be a good value
DEFAULT_BACK_URL = 'https://images.pokemoncard.io/images/assets/CardBack.jpg'

#card ids for basic energy; ptcgo export doesn't specify basic energy ids
BASIC_ENERGY = {
    'Darkness': 'col1-86',
    'Metal':    'col1-87',
    'Grass':    'col1-88',
    'Fire':     'col1-89',
    'Water':    'col1-90',
    'Lightning':'col1-91',
    'Psychic':  'col1-92',
    'Fighting': 'col1-93'
}

def main():
    # ========= command line arguments =========
    usage_string = "Usage: python pokemon_reader.py <input file> <output file> [options]\n\t\
                    Options:\n\t\
                    -cb [url]              : custom card back image URL\n\t\
                    -s [small | large]     : card image size (resolution), default: large\
                    "
    
    num_args = len(sys.argv)
    if num_args < 3:
        print(usage_string)
        return
    
    #process required arguments
    IN_PATH = sys.argv[1]
    OUT_PATH = sys.argv[2]
    if OUT_PATH[-5:].lower() != '.json':
        OUT_PATH = OUT_PATH+'.json'
    
    #process optional arguments
    i = 3
    BACK_URL = DEFAULT_BACK_URL
    IMAGE_SIZE = 'large'
    while i < num_args:
        if sys.argv[i] == '-cb':        #custom card back URL
            if num_args <= i+1:
                print(usage_string)
                return
            BACK_URL = sys.argv[i+1]
            i+=1
        
        if sys.argv[i] == '-s':         #image size specification
            if num_args <= i+1:
                print(usage_string)
                return
            provided_size = sys.argv[i+1].lower()
            if provided_size in ['small','large']:
                IMAGE_SIZE = provided_size
            else:
                print(usage_string)
                return
        i+=1
        #end loop
    
    #validate args
    if IMAGE_SIZE not in ['small','large']:
        print("ERROR: Invalid image size '"+IMAGE_SIZE+"'. Accepted sizes are small and large.")
        return
    createDeckFile(IN_PATH, OUT_PATH, BACK_URL, IMAGE_SIZE)


#TODO find usages of this and fix header, also use it in more places (trying to skip not crash on err)
#TODO also accomodate {M} energy format
def printSkipWarning(s):
    print("NOTICE: Skipping "+s)

def createDeckFile(IN_PATH, OUT_PATH, BACK_URL, IMAGE_SIZE):
    # ====== setup ==============    
    tcg.RestClient.configure(API_KEY)
    
    if OUT_PATH[-5:].lower() != '.json':
        OUT_PATH = OUT_PATH+'.json'
    
    deckFile = DeckFile(OUT_PATH)
    deckFile.start()
    
    if BACK_URL == "" or BACK_URL == None or BACK_URL is None: #lol I had no idea and did not care
        BACK_URL = DEFAULT_BACK_URL

    # ====== get card names ==============
    print("Reading deck info...")
    try:
        with open(IN_PATH, 'r') as f:
            file_content = f.read()
    except:
        print("ERROR: Could not open input file: '"+IN_PATH+"'. It may not exist at the specified location, or might be write protected.")
        return
    
    cards = []
    lines = file_content.split('\n')
    for i,line in enumerate(lines):
        line_regex = '(\d+) (.*) ([A-Z\-]+) (\d+)'
        match = re.match(line_regex, line)
        if match is None:
            printSkipWarning(line)
            continue
        card = {}
        card['quantity'] = match.group(1)
        card['name']     = match.group(2)
        card['set']      = match.group(3)
        card['id']       = match.group(4)
        card['image']    = None
        cards.append(card)
    
    # ====== get card images ==============
    #get front and back images for all card names. 'main' deck has the front face of all cards, and gives them a static back. 'double' deck has only double-sided cards, and includes both sides.
    #this code is EVIL because if you're running 24 islands it will make scryfall api calls 24 times, once for each island. this is unnecessary and wasteful but I just want it to work at the moment
    image_urls = []
    print("Using PokemonTCG API to get card image URLs...")
    num_main_cards = len(cards)
    for i,card in enumerate(cards):
        name     = card['name']
        set      = card['set']
        id       = card['id']
        
        # If there's a hyphen in the set name, ignore the hyphen suffix and try to get the card by 
        if '-' in set:
            #trim everything at & after the hyphen
            trimmed_set = re.sub('-.*','',set)
            try:
                candidates = tcg.Card.where(q='set.ptcgoCode:'+trimmed_set+' name:'+name)
                api_card = None
                for c in candidates:
                    if c.name == name:
                        api_card = c
                        print("WARNING: Making best guess for '"+name+"', set '"+set+"'... It has a weird set name. Check in the final product that it's the correct card.")
                        break
                assert(api_card is not None)
            except:
                print("ERROR: Could not fetch card: '"+name+"', set '"+set+"'. It's got a funky set name and I couldn't get around it.")
                printSkipWarning()
                continue
        # Otherwise, get the set ID to assemble a unique card ID
        else:
            #If the set name is Energy, the deck just wants basic energy and does not specify a set. Use hardcoded id
            if set == 'Energy':
                card_id = BASIC_ENERGY[card['name']] #map basic energy using a hardcoded card id
                card['name'] = card['name'] + ' Energy'
            # Otherwise, try to get the API set ID
            else:
                try:
                    set_id = tcg.Set.where(q='ptcgoCode:'+set)[0].id
                except:
                    print("ERROR: Could not fetch card: '"+name+"', set '"+set+"'. Could not resolve set name.")
                    return
                card_id = set_id+'-'+id
            
            # We have a card ID, get the card
            try:
                api_card = tcg.Card.find(card_id)
            except:
                print("ERROR: Could not fetch card: '"+name+"', set '"+set+"', id '"+card_id+"'. Could not resolve card.")
                return
        
        # We have a result from the API; record one of the image URLs
        if IMAGE_SIZE == 'small':
            image = api_card.images.small
        else IMAGE_SIZE == 'large':
            image = api_card.images.large
        card['image'] = image
            
        time.sleep(POLITENESS_DELAY)
    
    # ====== Convert card array to quantity-ignorant url array
    urls = []
    names = []
    for card in cards:
        for i in range(card['quantity']):
            urls.append(card['image'])
            name.append(card['name'])
    
    # ====== Assemble TTS object ==============
    print("Assembling output JSON file...")
    #THIS IS WHERE THE MAGIC HAPPENS
    deckFile.addDeck(names, urls, [BACK_URL], faceDown=True) #main deck! assume there will always be cards.
    #deckFile = DeckFile(OUT_PATH)
    deckFile.finish()
    #DONE
    print("Done.")
    return DOWNLOAD_PATH

# ====== functions and classes ==============

def cleanName(name):
    newName = name = re.sub("&#39;", "'", name) #replace strange apostrophe encodings. may have to do this with more punctiation.
    newName = re.sub(" ", "-", newName) #replace spaces with dashes, as this sometimes (not always) causes problems. See "Acorn Harvest")
    newName = re.sub(",", "", newName) #remove commas. this might be causing issues in some urllib versions
    return newName
    
#usage: instantiate a DeckFile, giving it a file path. One can then call addDeck() any number of times, and MUST call finish to close the file.
#All decks added will be a part of one "saved object", and all decks will be adjacent to each other when loaded in Tabletop Simulator.
class DeckFile:
    def __init__(self, path):
        self.path = path
        self.file = None
        self.num_decks = 0
        self.started = False
        self.finished = False
    
    def start(self):
        if (self.started == False):
            self.file = open(self.path,"w")
            self.file.write('{"ObjectStates":[')
            self.started = True
        
    def addDeck(self, names, frontURLs, backURLs=None, faceDown=False): #back URLs is optional. if omitted, uses default back. if length is one, uses the one element for every card. otherwise, treats fronts and backs as pairs.
        if (self.started == False):
            self.start()
        num_cards = len(names)
        
        if num_cards == 1: #for some reason TTS doesn't like decks with only one card! we need to write 1-card decks as just cards.
            if backURLs != None:
                self.addCard(names[0],frontURLs[0],backURLs[0], faceDown)
            else:
                self.addCard(names[0],frontURLs[0], faceDown = faceDown)
            return
        
        if (self.num_decks > 0):
            self.file.write(',')
        self.file.write('\n\t{\n\t\t"Name":"DeckCustom",\n\t\t"ContainedObjects":[')
        for i in range(num_cards):
            if (i!=0):
                self.file.write(',')
            self.file.write('\n\t\t\t{"CardID":'+str(100*(i+1))+',"Name":"Card","Nickname":"'+names[i]+'","Transform":{"posX":0,"posY":0,"posZ":0,"rotX":0,"rotY":180,"rotZ":180,"scaleX":1,"scaleY":1,"scaleZ":1}}')
        self.file.write('\n\t\t],\n\t\t"DeckIDs":[')
        for i in range(num_cards):
            if (i!=0):
                self.file.write(',')
            self.file.write(str(100*(i+1)))
        self.file.write('],\n\t\t"CustomDeck":{')
        
        for i in range(num_cards):
            if (backURLs == None):
                backURL = DEFAULT_BACK_URL
            elif (len(backURLs) == 1):
                backURL = backURLs[0]
            else:
                backURL = backURLs[i]
            if (i!=0):
                self.file.write(',')
            self.file.write('\n\t\t\t"'+str(i+1)+'":{"FaceURL":"'+frontURLs[i]+'","BackURL":"'+backURL+'","NumHeight":1,"NumWidth":1,"BackIsHidden":true}')
        
        rotZ = '180' if faceDown else '0'
        
        self.file.write('\n\t\t},\n\t\t"Transform":{"posX":'+str(self.num_decks*X_SPACE)+',"posY":0,"posZ":0,"rotX":0,"rotY":180,"rotZ":'+rotZ+',"scaleX":1,"scaleY":1,"scaleZ":1}\n\t}')
        self.num_decks += 1
    
    def addCard(self, name, frontURL, backURL=None, faceDown=False):
        if (self.started == False):
            self.start()

        if (self.num_decks > 0):
            self.file.write(',')
        self.file.write('\n\t{\n\t\t"Name":"Card",')
        self.file.write('\n\t\t"Nickname":"'+name+'",')
        self.file.write('\n\t\t"CardID":100,')
        
        if (backURL == None):
            backURL = DEFAULT_BACK_URL
        
        rotZ = '180' if faceDown else '0'
        
        self.file.write('\n\t\t"CustomDeck":{')
        self.file.write('\n\t\t\t"1":{"FaceURL":"'+frontURL+'","BackURL":"'+backURL+'","NumHeight":1,"NumWidth":1,"BackIsHidden":true}')
        self.file.write('\n\t\t},\n\t\t"Transform":{"posX":'+str(self.num_decks*X_SPACE)+',"posY":0,"posZ":0,"rotX":0,"rotY":180,"rotZ":'+rotZ+',"scaleX":1,"scaleY":1,"scaleZ":1}\n\t}')
        
        self.num_decks += 1
    
    def finish(self):
        self.file.write('\n]}')
        self.file.close()
        self.finished = True

if __name__ == '__main__':
    main()