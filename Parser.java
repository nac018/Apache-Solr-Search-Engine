package com.maven.quickstart;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.*;
import java.util.*;

public class App 
{
    public static void main( String[] args )
    {
        File htmls = new File("\\foxnews");
        File mapping = new File("\\URLtoHTML_fox_news.csv");
        FileWriter writer = null;
        try {
			writer = new FileWriter("\\output.txt");
		} catch (IOException e) {
			e.printStackTrace();
		}
        HashSet<String> edges = new HashSet<String>();
        HashMap<String,String> fileUrlMap = new HashMap<String,String>();
        HashMap<String,String> urlFileMap = new HashMap<String,String>();
        BufferedReader br = null;
        try {
			br = new BufferedReader(new FileReader(mapping));
			String line = br.readLine();
			while(line != null) {
				String[] arr = line.split(",");
				fileUrlMap.put(arr[0],arr[1]);
				urlFileMap.put(arr[1],arr[0]);
				line = br.readLine();
			}
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		} finally {
			if(br != null) {
				try {
					br.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
		}
        for(File file: htmls.listFiles()) {
        	Document doc = null;
			try {
				doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
			} catch (IOException e) {
				e.printStackTrace();
			}
        	Elements links = doc.select("a[href]");
        	for(Element link: links) {
        		String url = link.attr("abs:href").trim();
        		if(urlFileMap.containsKey(url)) {
        			edges.add(file.getName() + " " + urlFileMap.get(url));
        		}
        	}
        }
        BufferedWriter bw = new BufferedWriter(writer);
        try {
	        for(String str: edges) {
	        	bw.write(str);
	        	bw.write("\n");
	        }
	        System.out.println("OUTPUT FINISHED");
        } catch(IOException e) {
        	e.printStackTrace();
        } finally {
        	if(bw != null) {
				try {
					bw.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
        }
    }
}
