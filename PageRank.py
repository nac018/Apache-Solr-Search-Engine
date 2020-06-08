import networkx as nx

if __name__ == "__main__":
    G = nx.read_edgelist("output.txt", create_using=nx.DiGraph())
    pageRank = nx.pagerank(G, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',dangling=None) 
    with open("external_pageRankFile.txt","w") as output:
        for key in pageRank:
            output.write("/home/tommy/Downloads/solr-7.7.0/crawl_data/" + str(key) + "=" + str(pageRank[key]) + "\n")