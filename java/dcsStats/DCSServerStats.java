// Copyright 2016 Marcel Haupt
// http://marcel-haupt.eu/
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
// http ://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
// Github Project: https://github.com/cbacon93/DCSServerStats

package dcsStats;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.net.*;
import java.util.concurrent.ConcurrentLinkedQueue;


public class DCSServerStats {
	String host, port;
	String url;
	String pw;
	static ConcurrentLinkedQueue<String> eventQueue;
	
	DatagramSocket serverSocket;
	
	public static void main(String[] args) {
		if (args.length != 4) {
			System.out.println("Correct usage: java DCSServerStats [binding address] [port] [url] [password]");
			return;
		}
		
		//start stuff
		DCSServerStats server = new DCSServerStats(args[0], args[1], args[2], args[3]);
		eventQueue = new ConcurrentLinkedQueue<String>();
		HttpSendThread sendThread = server.new HttpSendThread();
		
		//start server and thread
		try {
			sendThread.start();
			server.start();
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	
	public DCSServerStats(String _host, String _port, String _url, String _pw) {
		host = _host;
		port = _port;
		url = _url;
		pw = _pw; 
	}
	
	private void start()  throws Exception {
		System.out.println("Starting Server");
		
		serverSocket = new DatagramSocket(Integer.parseInt(port));
    	byte[] receiveData = new byte[1024];
    	DatagramPacket receivePacket = new DatagramPacket(receiveData, receiveData.length);
    	
    	//receive loop
    	while(true) {
    		serverSocket.receive(receivePacket);
    		
    		String sentence = new String( receivePacket.getData(), 0, receivePacket.getLength());
    		System.out.println("Received: " + sentence);
    		
    		//add to queue
    		long unixTime = System.currentTimeMillis() / 1000L;
    		eventQueue.add(unixTime + ","+sentence);
    	}
	}
	
	public class HttpSendThread extends Thread {
		public void run() {
			while(true) {
				try {
					sendToDatabase();
					sleep(5000);
				} catch (Exception e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}
		
		private String getSplitString(String[] split, int id) {
			if (id < 0 || id >= split.length) return "";
			
			return split[id];
		}
		
		
		public void sendToDatabase() throws Exception {
			if (eventQueue.size() <= 0) {
				return;
			}
			
			
			
			//url data
			URL obj = new URL(url);
			HttpURLConnection con = (HttpURLConnection) obj.openConnection();

			//add reuqest header
			con.setRequestMethod("POST");
			con.setRequestProperty("User-Agent", "Mozilla/5.0");
			con.setRequestProperty("Accept-Language", "en-US,en;q=0.5");
			
			String urlParameters = "pw=" + pw;
			
			int evts = 0;
			while(eventQueue.size() > 0 && evts < 100) {
				String sentence = eventQueue.poll();
				
				//split data
				String[] split = sentence.split(",");
				
				urlParameters += 		"&time_" + evts + "=" + getSplitString(split, 0) +
										"&missiontime_" + evts + "=" + getSplitString(split, 1) + 
										"&event_" + evts + "=" + getSplitString(split, 2) +
										"&initid_" + evts + "=" + getSplitString(split, 3) +
										"&initcoa_" + evts + "=" + getSplitString(split, 4) +
										"&initgroupcat_" + evts + "=" + getSplitString(split, 5) +
										"&inittype_" + evts + "=" + getSplitString(split, 6) +
										"&initplayer_" + evts + "=" + getSplitString(split, 7) +
										"&eweaponcat_" + evts + "=" + getSplitString(split, 8) + 
										"&eweaponname_" + evts + "=" + getSplitString(split, 9) +
										"&targid_" + evts + "=" + getSplitString(split, 10) +
										"&targcoa_" + evts + "=" + getSplitString(split, 11) +
										"&targgroupcat_" + evts + "=" + getSplitString(split, 12) +
										"&targtype_" + evts + "=" + getSplitString(split, 13) +
										"&targplayer_" + evts + "=" + getSplitString(split, 14);
				
				evts++;
			
			}
			urlParameters += "&size=" + evts;
			System.out.println("Sending " + evts + " events...");
			
			//send post request
			con.setDoOutput(true);
			DataOutputStream wr = new DataOutputStream(con.getOutputStream());
			wr.writeBytes(urlParameters);
			wr.flush();
			wr.close();
			
			//get response
			int responseCode = con.getResponseCode();
			System.out.println("HTTP Response code: " + responseCode);
			
			//debugging
			
			BufferedReader in = new BufferedReader(
			        new InputStreamReader(con.getInputStream()));
			String inputLine;
			StringBuffer response = new StringBuffer();

			while ((inputLine = in.readLine()) != null) {
				response.append(inputLine);
			}
			in.close();
			
			//print result
			System.out.println("Response: " + response.toString());
		}
	}
}
