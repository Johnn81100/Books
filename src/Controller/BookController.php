<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'books', methods: ['GET'])]
    public function getAllBooks(BookRepository $bookRepository,SerializerInterface $serializer): JsonResponse
    {
        $bookList=$bookRepository->findAll();
        $jsonBookList= $serializer->serialize($bookList,'json',['groups'=>'getBooks']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }
    #[Route('/api/books/{id}', name: 'detailBook', methods: ['GET'])]
    public function getDetailBook(Book $book,SerializerInterface $serializer): JsonResponse
    {
            $jsonBook= $serializer->serialize($book,'json',['groups'=>'getBooks']);
            return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
      
    }
    #[Route('/api/books/{id}', name: 'deleteBook', methods: ['DELETE'])]
    public function deleteBook(Book $book,EntityManagerInterface $em): JsonResponse
    {
           $em->remove($book);
           $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
      
    }
    #[Route('/api/books', name: 'createBook', methods: ['POST'])]
    public function createBook(Request $request,EntityManagerInterface $em,ValidatorInterface $validator,SerializerInterface $serializer,UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        // dd($request->getPayload()->all());
        $book= $serializer->deserialize($request->getContent(),Book::class,'json');

        $errors= $validator->validate($book);
        if($errors->count()>0){
           
            return new JsonResponse($serializer->serialize(["message"=>$errors[0]->getMessage()],'json'), Response::HTTP_BAD_REQUEST, [], true);
            // throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
      
        $idAuthor= $request->getPayload()->all()['idAuthor'] ?? null;  
        if(isset($idAuthor)){
            $author = $em->getRepository(Author::class)->find($idAuthor);
            $book->setAuthor($author);
        }
        $em->persist($book);
        $em->flush();
        $jsonBook= $serializer->serialize($book,'json',['groups'=>'getBooks']);
       
        $location= $urlGenerator->generate('detailBook',[
            'id' => $book->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    #[Route('/api/books/{id}', name: 'updateBook', methods: ['PUT'])]
    public function updateBook(Request $request,Book $currentBook,EntityManagerInterface $em,SerializerInterface $serializer): JsonResponse
    {
        $updateBook= $serializer->deserialize($request->getContent(),Book::class,'json',[AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]);
        $idAuthor= $request->getPayload()->all()['idAuthor']?? -1;

        $updateBook->setAuthor($em->getRepository(Author::class)->find($idAuthor));
        
        $em->persist($updateBook);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
