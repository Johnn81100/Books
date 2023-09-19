<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthorController extends AbstractController
{
    #[Route('/api/authors', name: 'authors', methods: ['GET'])]
    public function getAllAuthor(AuthorRepository $AuthorRepository,SerializerInterface $serializer): JsonResponse
    {
        $authorList= $AuthorRepository->findAll();
        $jsonBookList= $serializer->serialize($authorList,'json',['groups'=>'getAuthors']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }
    #[Route('/api/authors/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getAuthor(Author $Author ,SerializerInterface $serializer): JsonResponse
    {
            $jsonAuthor= $serializer->serialize($Author,'json',['groups'=>'getAuthors']);
            return new JsonResponse( $jsonAuthor, Response::HTTP_OK, [], true);
      
    }
    #[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    public function deleteAuthor(Author $Author,AuthorRepository $AuthorRepository,EntityManagerInterface $em): JsonResponse
    {
        
        $allBooks=$Author->getBooks();
        foreach($allBooks as $book){
            $Author->removeBook($book);
        }

           $em->remove($Author);
           $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
      
    }
}
