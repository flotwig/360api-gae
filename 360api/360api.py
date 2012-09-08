import webapp2
import json
import urllib
import time
import xml.etree.ElementTree as ET

class apiHandler(webapp2.RequestHandler):
	def get(self):
		self.response.headers['Server'] = 'Xbox 360 Gamertag API'
		if 'gamertag' in self.request.GET:
			self.response.headers['Content-Type'] = 'application/json'
			docType = '{http://www.w3.org/1999/xhtml}'
			gamertag = self.request.GET['gamertag']
			msPageHandle = urllib.urlopen('http://gamercard.xbox.com/en-US/'+urllib.urlencode(gamertag)+'.card')
			msPage = msPageHandle.read()
			msPageHandle.close
			output = {}
			try:
				parse = ET.fromstring(msPage)
			finally:
				output['GamertagExists'] = False
				self.response.write(json.JSONEncoder().encode(output))
				return
			output['Gamertag'] = parse.find('.//*[@id="Gamertag"]').text
			output['Gamerscore'] = parse.find('.//*[@id="Gamerscore"]').text
			if output['Gamerscore']=='--':
				output['GamertagExists'] = False
				output['Gamerscore'] = 0
			else:
				output['GamertagExists'] = True
				output['Gamerscore'] = int(output['Gamerscore'])
			mainDivRaw = parse.find('.//'+docType+'div').attrib['class'].split(' ')
			output['Subscription'] = mainDivRaw[1]
			output['Gender'] = mainDivRaw[2]
			output['Pictures'] = {}
			output['Pictures']['Tile64px'] = parse.find('.//*[@id="Gamerpic"]').attrib['src']
			output['Pictures']['Tile32px'] = output['Pictures']['Tile64px'].replace('avatarpic-l.png','avatarpic-s.png')
			output['Pictures']['FullBody'] = output['Pictures']['Tile64px'].replace('avatarpic-l.png','avatar-body.png')
			reputationRaw = parse.findall('.//*[@class="RepContainer"]/*')
			del reputationRaw[0] # removes the label element because i am butt w/ xpath
			output['Reputation'] = 0
			for star in reputationRaw:
				if star.attrib['class']=='Star Full':
					output['Reputation'] += 1
				elif star.attrib['class']=='Star ThreeQuarter':
					output['Reputation'] += .75
				elif star.attrib['class']=='Star Quarter':
					output['Reputation'] += .25
				elif star.attrib['class']=='Star Half':
					output['Reputation'] += .5
			lastPlayedRaw = parse.findall('.//*[@id="PlayedGames"]/'+docType+'li/'+docType+'a');
			output['LastPlayed'] = []
			for lastPlayed in lastPlayedRaw:
				tmp = {}
				attribs = lastPlayed.findall(docType+'span');
				for attrib in attribs:
					if attrib.text.isdigit():
						tmp[attrib.attrib['class']] = int(attrib.text)
					else:
						tmp[attrib.attrib['class']] = attrib.text
				tmp['Pictures'] = {'Tile32px':lastPlayed.find(docType+'img').attrib['src']}
				tmp['LastPlayed'] = int(time.mktime(time.strptime(tmp['LastPlayed'],'%m/%d/%Y')))
				output['LastPlayed'].append(tmp)
			self.response.write(json.JSONEncoder().encode(output))
		else:
			self.response.headers['Content-Type'] = 'text/plain'
			self.response.write('Pass a gamertag in GET like so: /?gamertag=flotwig')

app = webapp2.WSGIApplication([('/', apiHandler)],debug=True)